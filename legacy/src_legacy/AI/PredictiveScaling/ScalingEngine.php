<?php

declare(strict_types=1);

namespace App\Component\Product\AI\PredictiveScaling;

final class ScalingEngine
{
    /** @param array<int,float> $series last N utilization points (0..100), equally spaced */
    public function forecast(array $series): array
    {
        $n = count($series);
        if ($n < 3) {
            return ['predicted' => ($series[$n - 1] ?? 0), 'action' => 'no-change', 'reason' => 'not-enough-data'];
        }
        // moving average of last 5 (or n)
        $m = array_slice($series, max(0, $n - 5));
        $avg = array_sum($m) / max(1, count($m));
        // slope via simple linear regression (x=1..n)
        $x = range(1, $n);
        $xmean = array_sum($x) / $n;
        $ymean = array_sum($series) / $n;
        $num = 0;
        $den = 0;
        for ($i = 0; $i < $n; ++$i) {
            $num += ($x[$i] - $xmean) * ($series[$i] - $ymean);
            $den += ($x[$i] - $xmean) ** 2;
        }
        $slope = $den > 0 ? $num / $den : 0.0;
        $pred = max(0.0, min(100.0, $series[$n - 1] + $slope));
        // decision
        $action = 'no-change';
        $reason = 'stable';
        if ($pred >= 75.0 || ($avg >= 70.0 && $slope > 0)) {
            $action = 'scale-up';
            $reason = 'predicted high load';
        }
        if ($pred <= 25.0 && $slope < 0) {
            $action = 'scale-down';
            $reason = 'predicted low load';
        }

        return ['avg' => $avg, 'slope' => $slope, 'predicted' => $pred, 'action' => $action, 'reason' => $reason];
    }
}
