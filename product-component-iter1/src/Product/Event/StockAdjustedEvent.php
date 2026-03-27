<?php
declare(strict_types=1);

namespace App\Component\Product\Event;

use App\Component\Product\Entity\Product;

final class StockAdjustedEvent
{
    public function __construct(
        public readonly Product $product,
        public readonly int $delta,
        public readonly int $newStock
    ) {}
}
