<?php
declare(strict_types=1);

namespace App\Component\Product\DTO\Product;

final class ProductVariantCreateDTO
{
    /** @param array<string,string> $options */
    public function __construct(
        public string $productId,
        public string $variantSku,
        public int $priceAmount,
        public string $priceCurrency,
        public int $stock = 0,
        public array $options = [] // ['color' => 'red', 'size' => 'L']
    ) {}
}
