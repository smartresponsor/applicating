<?php

declare(strict_types=1);

namespace App\Component\Product\RateControl;

use Doctrine\DBAL\Connection;

final class GlobalRateController
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Возвращает текущий режим: normal|soft|low_power. */
    public function mode(): string
    {
        // На деле берём метрики из Prometheus; здесь — из таблицы sys_metrics.
        $cpu = (float) ($this->db->fetchOne("SELECT value FROM sys_metrics WHERE name='cpu_pct' ORDER BY ts DESC LIMIT 1") ?? 30.0);
        if ($cpu >= 95.0) {
            return 'low_power';
        }
        if ($cpu >= 80.0) {
            return 'soft';
        }

        return 'normal';
    }

    /** Rate для плана с учётом глобального режима. */
    public function rateForPlan(string $plan): array
    {
        $base = match ($plan) {
            'vip' => [100.0, 200.0],
            'pro' => [50.0, 120.0],
            default => [20.0, 60.0],
        };
        $m = $this->mode();
        if ('soft' === $m) {
            return [$base[0] * 0.7, $base[1] * 0.7];
        }
        if ('low_power' === $m) {
            return [$base[0] * 0.3, $base[1] * 0.3];
        }

        return $base;
    }
}
