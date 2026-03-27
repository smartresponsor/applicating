<?php

declare(strict_types=1);

namespace App\Component\Product\PolicyLearning\Bandits;

interface BanditInterface
{
    public function choose(array $variants): array;

    public function update(string $variantId, float $reward): void;
}
