<?php
declare(strict_types=1);

namespace App\Component\Product\Event;

use App\Component\Product\Entity\Product;
use App\Component\Product\ValueObject\Money;

final class PriceChangedEvent
{
    public function __construct(
        public readonly Product $product,
        public readonly Money $oldPrice,
        public readonly Money $newPrice
    ) {}
}
