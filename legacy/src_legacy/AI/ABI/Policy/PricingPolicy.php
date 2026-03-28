<?php

declare(strict_types=1);

namespace App\Component\Product\AI\ABI\Policy;

final class PricingPolicy
{
    /**
     * Пример правил: gold → мягкие капы, стандарт → жёстче.
     *
     * @param array{tenant_priority:string, min_margin:float, floor:float, ceil:float} $ctx
     *
     * @return array{caps:array{cap_up:float,cap_down:float}, floor:float, ceil:float}
     */
    public function rules(array $ctx): array
    {
        $caps = 'gold' === $ctx['tenant_priority'] ? ['cap_up' => 0.3, 'cap_down' => 0.2] : ['cap_up' => 0.2, 'cap_down' => 0.3];

        return ['caps' => $caps, 'floor' => $ctx['floor'], 'ceil' => $ctx['ceil']];
    }
}
