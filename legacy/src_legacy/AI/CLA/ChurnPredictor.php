<?php

declare(strict_types=1);

namespace App\Component\Product\AI\CLA;

final class ChurnPredictor
{
    /**
     * Простой скоринг оттока.
     *
     * @param float $avgOrderValue      средний чек
     * @param int   $daysSinceLastOrder дней без покупок
     * @param int   $supportTickets     количество тикетов саппорта
     *
     * @return float churn score 0..1
     */
    public function score(float $avgOrderValue, int $daysSinceLastOrder, int $supportTickets): float
    {
        $s = 0.0;
        $s += min(1.0, $daysSinceLastOrder / 120.0);
        $s += min(1.0, $supportTickets / 5.0) * 0.3;
        $s -= min(1.0, $avgOrderValue / 200.0) * 0.2; // высокий AOV снижает риск

        return max(0.0, min(1.0, $s));
    }
}
