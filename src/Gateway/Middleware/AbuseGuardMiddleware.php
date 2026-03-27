<?php

declare(strict_types=1);

namespace App\Gateway\Middleware;

use App\Component\Product\AbuseGuard\AbuseAnalyzer;
use Doctrine\DBAL\Connection;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

final class AbuseGuardMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly Connection $db, private readonly AbuseAnalyzer $an)
    {
    }

    public function process(Request $request, Handler $handler): Response
    {
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? '0.0.0.0';
        $tenant = $request->getHeaderLine('X-Tenant') ?: 'tenantA';

        if ($this->an->isBanned($ip, $tenant)) {
            $this->an->log($ip, $tenant, 'blocked.banned', 429);

            return new JsonResponse(['error' => 'blocked', 'reason' => 'banned'], 429);
        }

        // Burst detection (threshold adjustable, demo=50/min)
        if ($this->an->isBursting($ip, $tenant, 50)) {
            $this->an->penalizeIP($ip, 30);
            $this->an->log($ip, $tenant, 'blocked.burst', 429);

            return new JsonResponse(['error' => 'blocked', 'reason' => 'burst'], 429);
        }

        $resp = $handler->handle($request);
        $this->an->log($ip, $tenant, 'ok', $resp->getStatusCode());

        return $resp;
    }
}
