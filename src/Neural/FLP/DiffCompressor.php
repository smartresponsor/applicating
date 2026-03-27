<?php

declare(strict_types=1);

namespace App\Component\Product\Neural\FLP;

final class DiffCompressor
{
    /** Наивное сжатие/квантование diff веса (демо). */
    public function compress(array $weights): array
    {
        $w = (float) ($weights['w'] ?? 1.0);

        return ['w' => round($w, 3)];
    }
}
