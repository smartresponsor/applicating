<?php
declare(strict_types=1);

namespace App\Component\Product\Interface\Product;

use App\Component\Product\Interface\Product\ProductInterface;

interface ProductCacheProviderInterface
{
    public function getById(string $id): ?ProductInterface;
    public function save(ProductInterface $product): void;
    public function invalidate(string $id): void;
    public function keyFor(string $id): string;
}
