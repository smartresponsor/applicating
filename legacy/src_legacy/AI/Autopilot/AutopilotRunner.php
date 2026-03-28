<?php

declare(strict_types=1);

namespace App\Component\Product\AI\Autopilot;

final class AutopilotRunner
{
    /** @param array<int,float> $series */
    public function forecast(array $series): array
    {
        $n = count($series);
        if ($n < 3) {
            return ['avg' => 0, 'slope' => 0, 'predicted' => $series[$n - 1] ?? 0, 'decision' => 'no-change'];
        }
        $m = array_slice($series, max(0, $n - 5));
        $avg = array_sum($m) / max(1, count($m));
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
        $decision = 'no-change';
        if ($pred >= 75.0 || ($avg >= 70.0 && $slope > 0)) {
            $decision = 'scale-up';
        }
        if ($pred <= 25.0 && $slope < 0) {
            $decision = 'scale-down';
        }

        return ['avg' => $avg, 'slope' => $slope, 'predicted' => $pred, 'decision' => $decision];
    }

    /** @return array<string,mixed> */
    public function runOnce(string $tenantId, array $series): array
    {
        $res = $this->forecast($series);
        $action = match ($res['decision']) {
            'scale-up' => 'hpa-patch',
            'scale-down' => 'hpa-patch',
            default => 'noop',
        };
        $outcome = 'noop' === $action ? 'skipped' : 'simulated';

        return [
            'tenant_id' => $tenantId,
            'avg' => $res['avg'],
            'slope' => $res['slope'],
            'predicted' => $res['predicted'],
            'decision' => $res['decision'],
            'action' => $action,
            'outcome' => $outcome,
            'details' => ['series' => $series],
        ];
    }
}
