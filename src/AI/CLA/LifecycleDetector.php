<?php

declare(strict_types=1);

namespace App\Component\Product\AI\CLA;

final class LifecycleDetector
{
    /**
     * Определяет стадию клиента по событиям активности.
     *
     * @param int $daysSinceSignup    дней с регистрации
     * @param int $daysSinceLastOrder дней с последнего заказа
     * @param int $ordersCount        всего заказов
     *
     * @return array{stage:string, rationale:string}
     */
    public function detect(int $daysSinceSignup, int $daysSinceLastOrder, int $ordersCount): array
    {
        if (0 == $ordersCount and $daysSinceSignup <= 14) {
            return ['stage' => 'new', 'rationale' => 'Недавняя регистрация, без заказов'];
        }
        if ($ordersCount > 0 and $daysSinceLastOrder <= 30) {
            return ['stage' => 'active', 'rationale' => 'Недавняя покупка'];
        }
        if ($ordersCount > 0 and $daysSinceLastOrder > 30 and $daysSinceLastOrder <= 90) {
            return ['stage' => 'at_risk', 'rationale' => 'Снижение частоты покупок'];
        }

        return ['stage' => 'lost', 'rationale' => 'Нет покупок > 90 дней'];
    }
}
