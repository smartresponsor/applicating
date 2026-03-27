<?php

declare(strict_types=1);

namespace App\Component\Product\AI\ABI\Pricing;

final class PricingEngine
{
    /**
     * Рассчёт цены с учётом эластичности спроса и гвардов.
     *
     * @param float                                $base        базовая цена (в у.е.)
     * @param float                                $elasticity  эластичность (отрицательное число, напр. -1.2)
     * @param float                                $demandIndex индекс спроса (0..2), 1 = базовый
     * @param float                                $minMargin   минимальная маржа (0..1)
     * @param float                                $unitCost    себестоимость (в у.е.)
     * @param array{cap_up?:float,cap_down?:float} $caps        пределы изменения (например, 0.2 = 20%)
     *
     * @return array{price:float,delta_pct:float,guard:string}
     */
    public function compute(
        float $base,
        float $elasticity,
        float $demandIndex,
        float $minMargin,
        float $unitCost,
        array $caps = ['cap_up' => 0.25, 'cap_down' => 0.25],
    ): array {
        // Модель: price' = base * (1 + k), где k = f(elasticity, demandIndex)
        // Простая аппроксимация: k = clamp((demandIndex - 1) * (-elasticity) / 2, -cap_down, cap_up)
        $capUp = (float) ($caps['cap_up'] ?? 0.25);
        $capDown = (float) ($caps['cap_down'] ?? 0.25);
        $rawK = (($demandIndex - 1.0) * (0 - $elasticity)) / 2.0;
        $k = max(-$capDown, min($capUp, $rawK));
        $price = round($base * (1.0 + $k), 2);

        // Guard: маржа
        $margin = $price > 0 ? ($price - $unitCost) / $price : 0.0;
        $guard = 'ok';
        if ($margin < $minMargin) {
            // Поднять цену до маржинального минимума
            $price = max($price, round($unitCost / (1.0 - $minMargin), 2));
            $guard = 'min_margin_guard';
        }

        return ['price' => $price, 'delta_pct' => $k, 'guard' => $guard];
    }
}
