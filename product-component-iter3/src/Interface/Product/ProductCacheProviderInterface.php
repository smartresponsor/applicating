<?php
declare(strict_types=1);

namespace App\Component\Product\Interface\Product;

interface ProductCacheProviderInterface
{
    public function getById(string $id): ?ProductInterface;
    public function save(ProductInterface $product): void;
    public function invalidate(string $id): void;
    public function keyFor(string $id): string;
}
