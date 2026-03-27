<?php

declare(strict_types=1);

namespace App\Component\Product\GraphQL;

use Doctrine\DBAL\Connection;

final class GraphQLTelemetryResolver
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Возвращает сводку телеметрии для AI Dashboard. */
    public function summary(string $tenantId): array
    {
        $qps = (float) ($this->db->fetchOne("SELECT COALESCE(AVG(qps),0) FROM telemetry_qps WHERE tenant_id=? AND ts >= (NOW()-INTERVAL '1 hour')", [$tenantId]) ?? 0.0);
        $err = (float) ($this->db->fetchOne("SELECT COALESCE(AVG(error_rate),0) FROM telemetry_errors WHERE tenant_id=? AND ts >= (NOW()-INTERVAL '1 hour')", [$tenantId]) ?? 0.0);
        $p95 = (float) ($this->db->fetchOne("SELECT COALESCE(AVG(p95_ms),0) FROM telemetry_latency WHERE tenant_id=? AND ts >= (NOW()-INTERVAL '1 hour')", [$tenantId]) ?? 0.0);

        return ['tenant' => $tenantId, 'qps_avg_1h' => $qps, 'error_rate_avg_1h' => $err, 'p95_ms_avg_1h' => $p95];
    }
}
