<?php
declare(strict_types=1);

namespace App\Component\Product\DTO\Product;

use App\Component\Product\ValueObject\Product\Money;

final class ProductUpdateDTO
{
    public function __construct(
        public ?Money $price = null,
        public ?int $stockDelta = null
    ) {}
}
