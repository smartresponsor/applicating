<?php

declare(strict_types=1);

namespace App\Component\Product\AI\ABI\Pricing;

final class ABTestManager
{
    /**
     * Деление трафика на вариации.
     *
     * @return array{bucket:string,variant:string}
     */
    public function assign(string $userId, array $variants = ['A', 'B'], array $weights = [0.5, 0.5]): array
    {
        $h = crc32($userId) / 0xFFFFFFFF;
        $cum = 0.0;
        for ($i = 0; $i < count($variants); ++$i) {
            $cum += $weights[$i] ?? 0.0;
            if ($h <= $cum) {
                return ['bucket' => $variants[$i], 'variant' => $variants[$i]];
            }
        }

        return ['bucket' => $variants[array_key_last($variants)], 'variant' => $variants[array_key_last($variants)]];
    }
}
