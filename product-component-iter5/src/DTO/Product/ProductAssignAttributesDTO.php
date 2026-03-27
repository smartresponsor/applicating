<?php
declare(strict_types=1);

namespace App\Component\Product\DTO\Product;

final class ProductAssignAttributesDTO
{
    /** @param array<string,string> $attributes */
    public function __construct(
        public string $productId,
        public array $attributes // ['material' => 'cotton', ...]
    ) {}
}
