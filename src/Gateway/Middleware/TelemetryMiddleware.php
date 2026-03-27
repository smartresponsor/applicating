<?php

declare(strict_types=1);

namespace App\Gateway\Middleware;

use Doctrine\DBAL\Connection;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

final class TelemetryMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function process(Request $request, Handler $handler): Response
    {
        $tenant = $request->getHeaderLine('X-Tenant') ?: 'tenantA';
        $t0 = microtime(true);
        try {
            $response = $handler->handle($request);
            $status = $response->getStatusCode();
        } catch (\Throwable $e) {
            $status = 500;
            throw $e;
        } finally {
            $latency = (microtime(true) - $t0) * 1000.0;
            $err = ($status >= 500) ? 1.0 : 0.0;
            // QPS аппроксимация: 1 запрос за интервал
            $this->db->insert('telemetry_qps', ['ts' => gmdate('c'), 'tenant_id' => $tenant, 'qps' => 1]);
            $this->db->insert('telemetry_errors', ['ts' => gmdate('c'), 'tenant_id' => $tenant, 'error_rate' => $err]);
            $this->db->insert('telemetry_latency', ['ts' => gmdate('c'), 'tenant_id' => $tenant, 'p95_ms' => $latency]);
        }

        return $response;
    }
}
