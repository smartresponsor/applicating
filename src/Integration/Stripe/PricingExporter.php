<?php

declare(strict_types=1);

namespace App\Component\Product\Integration\Stripe;

final class PricingExporter
{
    /**
     * Пример экспорта цен в Stripe Price API (заглушка).
     * В проде — использовать stripe-php SDK и обновлять price/tier.
     *
     * @param array<string,float> $skuToPrice
     *
     * @return array{ok:bool,count:int}
     */
    public function export(array $skuToPrice): array
    {
        // demo: просто вернуть count
        return ['ok' => true, 'count' => count($skuToPrice)];
    }
}
