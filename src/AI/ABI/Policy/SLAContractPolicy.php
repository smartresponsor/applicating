<?php

declare(strict_types=1);

namespace App\Component\Product\AI\ABI\Policy;

final class SLAContractPolicy
{
    /**
     * Правила по приоритету арендатора.
     *
     * @return array{contract_min:float, credit_multiplier:float}
     */
    public function forPriority(string $priority): array
    {
        return match ($priority) {
            'gold' => ['contract_min' => 99.9, 'credit_multiplier' => 1.5],
            default => ['contract_min' => 99.5, 'credit_multiplier' => 1.0],
        };
    }
}
