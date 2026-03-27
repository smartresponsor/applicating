<?php
declare(strict_types=1);

namespace App\Component\Product\DTO;

use App\Component\Product\ValueObject\Money;

final class ProductCreateDTO
{
    public function __construct(
        public string $sku,
        public Money $price,
        public int $initialStock = 0,
        /** @var array<string, array{name: string, slug: string, description?: string|null}> $i18n */
        public array $i18n = [] // ['en_US' => ['name' => 'X', 'slug' => 'x', 'description' => null]]
    ) {}
}
