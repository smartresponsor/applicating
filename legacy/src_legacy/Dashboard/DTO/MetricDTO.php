<?php

declare(strict_types=1);

namespace App\Component\Product\Dashboard\DTO;

final class MetricDTO
{
    public function __construct(
        public string $key,
        public string $label,
        public float $value,
        public ?string $unit = null,
    ) {
    }
}
