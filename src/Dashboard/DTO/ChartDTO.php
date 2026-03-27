<?php

declare(strict_types=1);

namespace App\Component\Product\Dashboard\DTO;

final class ChartDTO
{
    /** @param array<int,array{t:string,v:float}> $series */
    public function __construct(
        public string $id,
        public string $title,
        public array $series,
    ) {
    }
}
