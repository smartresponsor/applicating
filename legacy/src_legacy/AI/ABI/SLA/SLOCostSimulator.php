<?php

declare(strict_types=1);

namespace App\Component\Product\AI\ABI\SLA;

final class SLOCostSimulator
{
    /**
     * Оценка стоимости SLO по метрикам.
     *
     * @param float $p95Latency p95 латентность, мс
     * @param float $errorRate  доля ошибок, 0..1
     * @param int   $rps        запросы в секунду
     * @param float $costPerReq базовая стоимость 1 запроса, у.е.
     *
     * @return array{cost_per_day:float, tips:array<int,string>}
     */
    public function simulate(float $p95Latency, float $errorRate, int $rps, float $costPerReq = 0.0005): array
    {
        $trafficPerDay = $rps * 86400;
        $effectiveRequests = $trafficPerDay * (1.0 - min(1.0, max(0.0, $errorRate)));
        // Влияние латентности: штраф при p95 > 400мс
        $latencyPenalty = ($p95Latency > 400.0) ? (1.0 + min(1.0, ($p95Latency - 400.0) / 800.0)) : 1.0;
        $cost = round($effectiveRequests * $costPerReq * $latencyPenalty, 4);

        $tips = [];
        if ($p95Latency > 400.0) {
            $tips[] = 'Снизьте p95 < 400мс: кеш/лимиты/индексы.';
        }
        if ($errorRate > 0.02) {
            $tips[] = 'Ошибка >2%: проверьте ретраи/таймауты/бэкенды.';
        }
        if ($rps > 1000) {
            $tips[] = 'Высокий трафик: рассмотрите отдельный пул/шардирование.';
        }

        return ['cost_per_day' => $cost, 'tips' => $tips];
    }
}
