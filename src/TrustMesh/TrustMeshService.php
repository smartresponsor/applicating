<?php

declare(strict_types=1);

namespace App\Component\Product\TrustMesh;

use Doctrine\DBAL\Connection;

/**
 * Хранение и расчёт TrustScore для агентов/арендаторов.
 */
final class TrustMeshService
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Репорт метрик доверия (SLA, refund_rate, uptime и т.д.). */
    public function report(string $tenantId, float $sla, float $refundRate, float $uptime): void
    {
        $this->db->insert('trustmesh_reports', [
            'ts' => gmdate('c'), 'tenant_id' => $tenantId, 'sla' => $sla, 'refund_rate' => $refundRate, 'uptime' => $uptime,
        ]);
    }
}
