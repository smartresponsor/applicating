<?php

declare(strict_types=1);

namespace App\Component\Product\Predictive;

final class OutcomeScorer
{
    /** Взвешенная оценка исхода. Веса можно править в config. */
    public function score(array $sim, array $weights = ['reward' => 0.6, 'deny' => -0.3, 'latency' => -0.1]): float
    {
        $s = ($sim['expected_reward'] ?? 0) * ($weights['reward'] ?? 0.6)
           + (1.0 - ($sim['expected_deny_rate'] ?? 0)) * ($weights['deny'] ?? -0.3)
           + max(0.0, 1.0 - (($sim['expected_latency_ms'] ?? 0) / 1000.0)) * ($weights['latency'] ?? -0.1);

        return round($s, 4);
    }
}
