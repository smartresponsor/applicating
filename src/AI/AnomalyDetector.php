<?php

declare(strict_types=1);

namespace App\Component\Product\AI;

use Doctrine\DBAL\Connection;

final class AnomalyDetector
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Находит выбросы по usage: сравнение последнего часа со средним за 24ч. */
    public function scan(string $tenantId): array
    {
        $hour = (float) ($this->db->fetchOne("SELECT COALESCE(SUM(amount_usd),0) FROM tenant_usage WHERE tenant_id=? AND ts >= (NOW() - INTERVAL '1 hour')", [$tenantId]) ?? 0.0);
        $day = (float) ($this->db->fetchOne("SELECT COALESCE(SUM(amount_usd),0) FROM tenant_usage WHERE tenant_id=? AND ts >= (NOW() - INTERVAL '24 hour')", [$tenantId]) ?? 0.0);
        $avgPerHour = $day > 0 ? $day / 24.0 : 0.0;
        $ratio = $avgPerHour > 0 ? $hour / $avgPerHour : 0.0;
        $anomaly = $ratio >= 2.5; // более чем x2.5 от среднего — сигнал

        return ['hour' => $hour, 'avg_per_hour' => $avgPerHour, 'ratio' => $ratio, 'anomaly' => $anomaly];
    }
}
