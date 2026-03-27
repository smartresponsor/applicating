<?php

declare(strict_types=1);

namespace App\Component\Product\AI\ABI\Pricing;

final class Guardrails
{
    public function clampByFloorCeil(float $price, float $floor, float $ceil): array
    {
        $g = 'ok';
        if ($price < $floor) {
            $price = $floor;
            $g = 'floor';
        }
        if ($price > $ceil) {
            $price = $ceil;
            $g = 'ceil';
        }

        return ['price' => $price, 'guard' => $g];
    }
}
