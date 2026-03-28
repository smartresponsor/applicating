<?php

declare(strict_types=1);

namespace App\Component\Product\SelfHealing;

use App\Component\Product\SelfHealing\DTO\IncidentReport;
use Doctrine\DBAL\Connection;

final class AnomalyDetector
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** @return array<int,IncidentReport> */
    public function scan(string $tenantId): array
    {
        $rows = $this->db->fetchAllAssociative(
            'SELECT key, severity FROM alert_rules WHERE enabled=TRUE'
        );
        $incidents = [];
        foreach ($rows as $r) {
            // Наивная проверка: если среднее значение метрики > 0.3 → firing
            $val = (float) ($this->db->fetchOne(
                "SELECT COALESCE(AVG(value),0) FROM metrics_timeseries WHERE name=? AND ts >= (NOW() - INTERVAL '5 minutes')",
                [$r['key']]
            ) ?: 0.0);
            if ($val > 0.3) {
                $incidents[] = new IncidentReport($tenantId, (string) $r['key'], 'firing', "threshold breach: {$val}", gmdate('c'));
            }
        }

        return $incidents;
    }
}
