<?php

declare(strict_types=1);

namespace App\Component\Product\Sentient;

use Doctrine\DBAL\Connection;

final class AnomalyPredictor
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Простейшая детекция аномалий на скользящем окне по latency и sla_ok. */
    public function predict(int $minutes = 60): array
    {
        $avgLat = (float) ($this->db->fetchOne('SELECT AVG(latency_ms) FROM sentient_feedback WHERE ts>NOW()-INTERVAL ? ', [$minutes.' minutes']) ?? 0.0);
        $avgSla = (float) ($this->db->fetchOne('SELECT AVG(sla_ok) FROM sentient_feedback WHERE ts>NOW()-INTERVAL ? ', [$minutes.' minutes']) ?? 1.0);
        $anom = ($avgLat > 600.0) || ($avgSla < 0.95);
        $riskDrift = (float) ($this->db->fetchOne('SELECT AVG(risk) FROM sentient_feedback WHERE ts>NOW()-INTERVAL ? ', [$minutes.' minutes']) ?? 0.5);

        return ['anomaly' => $anom, 'avg_latency_ms' => round($avgLat, 2), 'avg_sla_ok' => round($avgSla, 4), 'risk_drift' => round($riskDrift, 4)];
    }
}
