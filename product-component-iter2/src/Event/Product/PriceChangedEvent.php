<?php
declare(strict_types=1);

namespace App\Component\Product\Event\Product;

use App\Component\Product\Interface\Product\ProductEventInterface;
use App\Component\Product\Interface\Product\ProductInterface;
use App\Component\Product\ValueObject\Product\Money;

final class PriceChangedEvent implements ProductEventInterface
{
    private \DateTimeImmutable $at;
    public function __construct(
        public readonly ProductInterface $product,
        public readonly Money $oldPrice,
        public readonly Money $newPrice
    ) { $this->at = new \DateTimeImmutable(); }

    public function eventName(): string { return 'product.price_changed'; }
    public function occurredAt(): \DateTimeImmutable { return $this->at; }
    public function payload(): array
    {
        return [
            'product_id' => $this->product->id(),
            'old_price_amount' => $this->oldPrice->amount(),
            'old_price_currency' => $this->oldPrice->currency(),
            'new_price_amount' => $this->newPrice->amount(),
            'new_price_currency' => $this->newPrice->currency(),
        ];
    }
}
