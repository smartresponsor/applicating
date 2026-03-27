<?php
declare(strict_types=1);

namespace App\Component\Product\Event\Product;

use App\Component\Product\Interface\Product\ProductEventInterface;
use App\Component\Product\Interface\Product\ProductInterface;

final class ProductCreatedEvent implements ProductEventInterface
{
    private \DateTimeImmutable $at;
    public function __construct(public readonly ProductInterface $product)
    {
        $this->at = new \DateTimeImmutable();
    }
    public function eventName(): string { return 'product.created'; }
    public function occurredAt(): \DateTimeImmutable { return $this->at; }
    public function payload(): array
    {
        return [
            'product_id' => $this->product->id(),
            'product_sku' => (string)$this->product->sku(),
            'product_status' => $this->product->status()->value,
            'product_price_amount' => $this->product->price()->amount(),
            'product_price_currency' => $this->product->price()->currency(),
        ];
    }
}
