<?php

declare(strict_types=1);

namespace App\Component\Product\Telemetry;

use Doctrine\DBAL\Connection;

final class TelemetryCollector
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function recordQps(string $tenantId, float $qps): void
    {
        $this->db->insert('telemetry_qps', ['ts' => gmdate('c'), 'tenant_id' => $tenantId, 'qps' => $qps]);
    }

    public function recordErrorRate(string $tenantId, float $err): void
    {
        $this->db->insert('telemetry_errors', ['ts' => gmdate('c'), 'tenant_id' => $tenantId, 'error_rate' => $err]);
    }

    public function recordLatency(string $tenantId, float $p95_ms): void
    {
        $this->db->insert('telemetry_latency', ['ts' => gmdate('c'), 'tenant_id' => $tenantId, 'p95_ms' => $p95_ms]);
    }
}
