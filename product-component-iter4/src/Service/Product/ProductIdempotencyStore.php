<?php
declare(strict_types=1);

namespace App\Component\Product\Service\Product;

use App\Component\Product\Idempotency\Product\ProductIdempotency;
use Doctrine\ORM\EntityManagerInterface;

final class ProductIdempotencyStore
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function exists(string $key): bool
    {
        return (bool) $this->em->getRepository(ProductIdempotency::class)->find($key);
    }

    public function putIfAbsent(string $key): bool
    {
        if ($this->exists($key)) {
            return false;
        }
        $this->em->persist(new ProductIdempotency($key));
        // Фиксируется транзакцией вызывающего сервиса
        return true;
    }

    public function markProcessed(string $key): void
    {
        $found = $this->em->getRepository(ProductIdempotency::class)->find($key);
        if ($found instanceof ProductIdempotency) {
            $found->markProcessed();
        }
    }
}
