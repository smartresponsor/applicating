<?php

declare(strict_types=1);

namespace App\Component\Product\TrustMesh;

use Doctrine\DBAL\Connection;

/**
 * Агрегация TrustScore и влияния на федерацию/экономику.
 */
final class TrustScoreAggregator
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Простой скоринг: SLA (0..1)*0.6 + (1-refund)*0.3 + uptime*0.1 */
    public function compute(string $tenantId): float
    {
        $row = $this->db->fetchAssociative('SELECT COALESCE(AVG(sla),0) sla, COALESCE(AVG(refund_rate),0) refund, COALESCE(AVG(uptime),0) uptime FROM trustmesh_reports WHERE tenant_id=?', [$tenantId]);
        $sla = (float) ($row['sla'] ?? 0.0);
        $refund = (float) ($row['refund'] ?? 0.0);
        $uptime = (float) ($row['uptime'] ?? 0.0);
        $score = max(0.0, min(1.0, $sla * 0.6 + (1.0 - $refund) * 0.3 + $uptime * 0.1));
        $this->db->insert('trustmesh_scores', [
            'ts' => gmdate('c'), 'tenant_id' => $tenantId, 'score' => $score,
        ]);

        return $score;
    }

    /** Последний известный TrustScore. */
    public function latest(string $tenantId): float
    {
        $val = $this->db->fetchOne('SELECT score FROM trustmesh_scores WHERE tenant_id=? ORDER BY ts DESC LIMIT 1', [$tenantId]);

        return (float) ($val ?? 0.0);
    }
}
