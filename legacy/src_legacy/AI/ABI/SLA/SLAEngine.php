<?php

declare(strict_types=1);

namespace App\Component\Product\AI\ABI\SLA;

final class SLAEngine
{
    /**
     * Рассчитать рекомендуемый SLA-тир и компенсацию при нарушении.
     *
     * @param float  $uptime      текущая доступность за период (в %, 0..100)
     * @param float  $contractMin минимальный контрактный SLA (в %, напр. 99.5)
     * @param float  $mttr        среднее время восстановления, минут
     * @param float  $mtbf        среднее время между сбоями, часов
     * @param string $priority    приоритет арендатора: gold|standard
     *
     * @return array{
     *   recommended_tier:string,
     *   credit_cents:int,
     *   breach:bool,
     *   details:array<string,mixed>
     * }
     */
    public function evaluate(float $uptime, float $contractMin, float $mttr, float $mtbf, string $priority = 'standard'): array
    {
        $breach = $uptime < $contractMin;
        // Рекомендация тира: на основе текущей стабильности (MTTR/MTBF)
        $stability = ($mtbf > 0) ? (60.0 * $mtbf) / max(1.0, $mttr) : 0.0; // чем выше, тем стабильнее
        $tier = 'SLA-99.5';
        if ($stability > 600 && $uptime >= 99.9) {
            $tier = 'SLA-99.95';
        } elseif ($stability > 200 && $uptime >= 99.7) {
            $tier = 'SLA-99.9';
        } elseif ($stability > 80 && $uptime >= 99.5) {
            $tier = 'SLA-99.7';
        }

        // Компенсация: базируется на разнице до контракта
        $gap = max(0.0, $contractMin - $uptime); // в процентах
        $baseCredit = (int) round($gap * 1000); // условные 1000 центов за 1% gap
        if ('gold' === $priority) {
            $baseCredit = (int) round($baseCredit * 1.5);
        }

        return [
            'recommended_tier' => $tier,
            'credit_cents' => $breach ? $baseCredit : 0,
            'breach' => $breach,
            'details' => [
                'uptime' => $uptime,
                'contract_min' => $contractMin,
                'mttr_min' => $mttr,
                'mtbf_hr' => $mtbf,
                'stability' => $stability,
                'gap_pct' => $gap,
            ],
        ];
    }
}
