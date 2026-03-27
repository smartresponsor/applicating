<?php
declare(strict_types=1);

namespace App\Component\Product\DTO\Product;

use App\Component\Product\ValueObject\Product\Money;

final class ProductCreateDTO
{
    public function __construct(
        public string $sku,
        public Money $price,
        public int $initialStock = 0,
        /** @var array<string, array{name: string, slug: string, description?: string|null}> */
        public array $i18n = []
    ) {}
}
