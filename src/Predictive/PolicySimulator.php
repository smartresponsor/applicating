<?php

declare(strict_types=1);

namespace App\Component\Product\Predictive;

use Doctrine\DBAL\Connection;

final class PolicySimulator
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Прогоняет N симуляций и возвращает агрегированные метрики (ожидаемый reward/denies/latency). */
    public function simulate(string $policy, array $effects, int $n = 1000): array
    {
        $sumReward = 0.0;
        $denies = 0;
        $lat = 0.0;
        mt_srand(42);
        for ($i = 0; $i < $n; ++$i) {
            $trust = mt_rand() / mt_getrandmax(); // [0,1]
            $base = $trust + (($effects['discount'] ?? 0) * 0.3);
            $reward = $base > 0.7 ? 1.0 : 0.0;
            $deny = ($effects['rate_limit'] ?? 100) < 10 ? 1 : 0;
            $lat += 50 + ($deny ? 10 : 0);
            $sumReward += $reward;
            $denies += $deny;
        }

        return [
            'policy' => $policy,
            'runs' => $n,
            'expected_reward' => round($sumReward / $n, 4),
            'expected_deny_rate' => round($denies / max(1, $n), 4),
            'expected_latency_ms' => round($lat / max(1, $n), 2),
        ];
    }
}
