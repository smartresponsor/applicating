<?php

declare(strict_types=1);

namespace App\Gateway\Middleware;

use App\Component\Product\RateControl\GlobalRateController;
use App\Component\Product\RateControl\QuarantineManager;
use Doctrine\DBAL\Connection;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

final class RateGuardMiddleware implements MiddlewareInterface
{
    /** @var array<string,array{last:float,tokens:float,rate:float,burst:float}> */
    private array $buckets = [];

    public function __construct(private readonly Connection $db, private readonly GlobalRateController $ctl, private readonly QuarantineManager $q)
    {
    }

    private function limits(string $tenant): array
    {
        $plan = (string) ($this->db->fetchOne('SELECT plan FROM smartcloud_tenants WHERE tenant_id=?', [$tenant]) ?? 'standard');

        return $this->ctl->rateForPlan($plan);
    }

    private function allow(string $tenant): bool
    {
        if (!isset($this->buckets[$tenant])) {
            [$r,$b] = $this->limits($tenant);
            $this->buckets[$tenant] = ['last' => microtime(true), 'tokens' => $b, 'rate' => $r, 'burst' => $b];
        }
        $b = &$this->buckets[$tenant];
        $now = microtime(true);
        $elapsed = $now - $b['last'];
        $b['last'] = $now;
        $b['tokens'] = min($b['burst'], $b['tokens'] + $elapsed * $b['rate']);
        if ($b['tokens'] >= 1) {
            --$b['tokens'];

            return true;
        }

        return false;
    }

    public function process(Request $request, Handler $handler): Response
    {
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? '0.0.0.0';
        $tenant = $request->getHeaderLine('X-Tenant') ?: 'tenantA';

        if ($this->q->isQuarantinedIp($ip) || $this->q->isQuarantinedTenant($tenant)) {
            return new JsonResponse(['error' => 'quarantined'], 429);
        }

        if (!$this->allow($tenant)) {
            return new JsonResponse(['error' => 'rate_limited', 'mode' => $this->ctl->mode()], 429);
        }

        return $handler->handle($request);
    }
}
