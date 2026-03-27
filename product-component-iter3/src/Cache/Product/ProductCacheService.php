<?php
declare(strict_types=1);

namespace App\Component\Product\Cache\Product;

use App\Component\Product\Interface\Product\ProductCacheProviderInterface;
use App\Component\Product\Interface\Product\ProductInterface;
use Psr\Cache\CacheItemPoolInterface;

final class ProductCacheService implements ProductCacheProviderInterface
{
    public function __construct(private readonly CacheItemPoolInterface $pool) {}

    public function keyFor(string $id): string { return 'product:read:' . $id; }

    public function getById(string $id): ?ProductInterface
    {
        $item = $this->pool->getItem($this->keyFor($id));
        $v = $item->get();
        return $v instanceof ProductInterface ? $v : null;
    }

    public function save(ProductInterface $product): void
    {
        $item = $this->pool->getItem($this->keyFor($product->id()));
        $item->set($product);
        $this->pool->save($item);
    }

    public function invalidate(string $id): void
    {
        $this->pool->deleteItem($this->keyFor($id));
    }
}
