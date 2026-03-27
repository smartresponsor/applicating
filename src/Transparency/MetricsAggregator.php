<?php

declare(strict_types=1);

namespace App\Component\Product\Transparency;

use Doctrine\DBAL\Connection;

final class MetricsAggregator
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Возвращает агрегированные публичные метрики по последним окнам времени. */
    public function aggregate(int $minutes = 60): array
    {
        $risk = (float) ($this->db->fetchOne('SELECT AVG(final_score) FROM policy_orchestrator_decisions WHERE ts>NOW()-INTERVAL ? ', [$minutes.' minutes']) ?? 0.5);
        $sla = (float) ($this->db->fetchOne('SELECT AVG(sla_ok) FROM sentient_feedback WHERE ts>NOW()-INTERVAL ? ', [$minutes.' minutes']) ?? 0.97);
        $lat = (float) ($this->db->fetchOne('SELECT AVG(latency_ms) FROM sentient_feedback WHERE ts>NOW()-INTERVAL ? ', [$minutes.' minutes']) ?? 300.0);
        $gov = (int) ($this->db->fetchOne('SELECT COUNT(*) FROM policy_governance_actions WHERE ts>NOW()-INTERVAL ? ', [$minutes.' minutes']) ?? 0);
        $aud = (int) ($this->db->fetchOne('SELECT COUNT(*) FROM system_audit_ledger WHERE ts>NOW()-INTERVAL ? ', [$minutes.' minutes']) ?? 0);

        return [
            'avg_final_risk' => round($risk, 4),
            'avg_sla_ok' => round($sla, 4),
            'avg_latency_ms' => round($lat, 2),
            'governance_actions' => $gov,
            'audit_appends' => $aud,
            'window_min' => $minutes,
        ];
    }
}
