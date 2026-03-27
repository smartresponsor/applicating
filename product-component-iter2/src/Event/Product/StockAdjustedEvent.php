<?php
declare(strict_types=1);

namespace App\Component\Product\Event\Product;

use App\Component\Product\Interface\Product\ProductEventInterface;
use App\Component\Product\Interface\Product\ProductInterface;

final class StockAdjustedEvent implements ProductEventInterface
{
    private \DateTimeImmutable $at;
    public function __construct(
        public readonly ProductInterface $product,
        public readonly int $delta,
        public readonly int $newStock
    ) { $this->at = new \DateTimeImmutable(); }

    public function eventName(): string { return 'product.stock_adjusted'; }
    public function occurredAt(): \DateTimeImmutable { return $this->at; }
    public function payload(): array
    {
        return [
            'product_id' => $this->product->id(),
            'delta' => $this->delta,
            'new_stock' => $this->newStock,
        ];
    }
}
