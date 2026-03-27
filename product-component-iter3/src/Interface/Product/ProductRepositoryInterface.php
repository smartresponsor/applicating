<?php
declare(strict_types=1);

namespace App\Component\Product\Interface\Product;

interface ProductRepositoryInterface
{
    public function byId(string $id): ?ProductInterface;
    public function bySku(string $sku): ?ProductInterface;
    public function save(ProductInterface $product): void;
}
