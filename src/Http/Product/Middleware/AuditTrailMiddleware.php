<?php

declare(strict_types=1);

namespace App\Component\Product\Http\Product\Middleware;

use App\Component\Product\Audit\AuditLogger;
use App\Component\Product\Security\PiiMasker;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

final class AuditTrailMiddleware
{
    public function __construct(private readonly AuditLogger $logger, private readonly PiiMasker $masker)
    {
    }

    public function __invoke(ResponseEvent $event): void
    {
        $req = $event->getRequest();
        $res = $event->getResponse();

        $jwt = $req->attributes->get('jwt');
        $actor = is_array($jwt) && isset($jwt['sub']) ? (string) $jwt['sub'] : ((string) $req->headers->get('X-API-Key') ?: ($req->getClientIp() ?? 'anon'));

        // Log only catalog endpoints to control volume (adjust as needed)
        $path = $req->getPathInfo();
        if (!str_starts_with($path, '/api/catalog')) {
            return;
        }

        $payload = [
            'q' => $req->query->get('q'),
            'status' => $req->query->get('status'),
            'cursor' => $req->query->get('cursor'),
            'limit' => $req->query->get('limit'),
        ];
        $payload = $this->masker->mask($payload);

        $this->logger->log(
            actor: $actor,
            method: $req->getMethod(),
            path: $path,
            status: $res->getStatusCode(),
            ip: $req->getClientIp() ?: null,
            ua: $req->headers->get('User-Agent') ?: null,
            payload: $payload
        );
    }
}
