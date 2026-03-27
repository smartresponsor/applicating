<?php

declare(strict_types=1);

namespace App\Component\Product\AI\ABI\Forecast;

final class RevenueForecast
{
    /**
     * Простой прогноз на основе среднего и тренда (линейная регрессия).
     *
     * @param array<int,float> $series Месячная выручка (последние N точек)
     *
     * @return array{avg:float,slope:float,predicted:float}
     */
    public function predict(array $series): array
    {
        $n = count($series);
        if (0 === $n) {
            return ['avg' => 0.0, 'slope' => 0.0, 'predicted' => 0.0];
        }
        $avg = array_sum($series) / $n;
        $x = range(1, $n);
        $xmean = array_sum($x) / $n;
        $ymean = $avg;
        $num = 0.0;
        $den = 0.0;
        for ($i = 0; $i < $n; ++$i) {
            $num += ($x[$i] - $xmean) * ($series[$i] - $ymean);
            $den += ($x[$i] - $xmean) ** 2;
        }
        $slope = $den > 0 ? $num / $den : 0.0;
        $pred = max(0.0, $series[$n - 1] + $slope);

        return ['avg' => $avg, 'slope' => $slope, 'predicted' => $pred];
    }
}
