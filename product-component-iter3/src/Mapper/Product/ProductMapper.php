<?php
declare(strict_types=1);

namespace App\Component\Product\Mapper\Product;

use App\Component\Product\Entity\Product\Product;

final class ProductMapper
{
    /** @return array<string, mixed> */
    public function toArray(Product $p): array
    {
        return [
            'id' => $p->id(),
            'sku' => (string)$p->sku(),
            'price_amount' => $p->price()->amount(),
            'price_currency' => $p->price()->currency(),
            'stock' => $p->stock(),
            'status' => $p->status()->value,
            'created_at' => $p->createdAt()->format(DATE_ATOM),
            'updated_at' => $p->updatedAt()->format(DATE_ATOM),
        ];
    }
}
