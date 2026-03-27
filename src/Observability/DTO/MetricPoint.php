<?php

declare(strict_types=1);

namespace App\Component\Product\Observability\DTO;

final class MetricPoint
{
    public function __construct(
        public string $name,
        /** @var array<string,string> */
        public array $labels,
        public float $value,
        public string $ts,
    ) {
    }
}
