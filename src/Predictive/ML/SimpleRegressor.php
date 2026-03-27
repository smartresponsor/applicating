<?php

declare(strict_types=1);

namespace App\Component\Product\Predictive\ML;

/** Заглушка: простая линейная регрессия по 3 признакам. */
final class SimpleRegressor
{
    private array $w = [0.5, 0.3, 0.2]; // trust, discount, rate_limit

    public function predict(float $trust, float $discount, float $rateLimit): float
    {
        $x1 = $trust;
        $x2 = $discount;
        $x3 = min(1.0, $rateLimit / 200.0);
        $y = $this->w[0] * $x1 + $this->w[1] * $x2 + $this->w[2] * $x3;

        return max(0.0, min(1.0, $y));
    }
}
