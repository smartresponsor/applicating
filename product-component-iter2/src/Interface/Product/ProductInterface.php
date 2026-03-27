<?php
declare(strict_types=1);

namespace App\Component\Product\Interface\Product;

use App\Component\Product\Enum\ProductStatus;
use App\Component\Product\ValueObject\Product\Money;
use App\Component\Product\ValueObject\Product\Sku;

interface ProductInterface
{
    public function id(): string;
    public function sku(): Sku;
    public function price(): Money;
    public function stock(): int;
    public function status(): ProductStatus;
    public function createdAt(): \DateTimeImmutable;
    public function updatedAt(): \DateTimeImmutable;
}
