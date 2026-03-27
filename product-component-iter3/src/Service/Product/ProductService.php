<?php
declare(strict_types=1);

namespace App\Component\Product\Service\Product;

use App\Component\Product\DTO\Product\ProductCreateDTO;
use App\Component\Product\DTO\Product\ProductUpdateDTO;
use App\Component\Product\Entity\Product\Product;
use App\Component\Product\Entity\Product\ProductI18n;
use App\Component\Product\Event\Product\PriceChangedEvent;
use App\Component\Product\Event\Product\ProductCreatedEvent;
use App\Component\Product\Event\Product\ProductUpdatedEvent;
use App\Component\Product\Event\Product\StockAdjustedEvent;
use App\Component\Product\Interface\Product\ProductInterface;
use App\Component\Product\Interface\Product\ProductRepositoryInterface;
use App\Component\Product\Interface\Product\ProductServiceInterface;
use App\Component\Product\Interface\Product\ProductCacheProviderInterface;
use App\Component\Product\ReadModel\Product\ProductRead;
use App\Component\Product\ValueObject\Product\Money;
use App\Component\Product\ValueObject\Product\Sku;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

final class ProductService implements ProductServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly ProductRepositoryInterface $repo,
        private readonly ProductCacheProviderInterface $cache,
        private readonly ProductOutboxPublisher $outbox
    ) {}

    public function create(ProductCreateDTO $dto): ProductInterface
    {
        return $this->em->wrapInTransaction(function() use ($dto) {
            $product = new Product(new Sku($dto->sku), $dto->price, $dto->initialStock);
            foreach ($dto->i18n as $locale => $data) {
                $product->addTranslation(new ProductI18n(
                    $product, $locale, $data['name'], $data['slug'], $data['description'] ?? null
                ));
            }
            $this->repo->save($product);

            $title = $dto->i18n['en_US']['name'] ?? ($dto->i18n[array_key_first($dto->i18n)]['name'] ?? '—');
            $read = new ProductRead(
                $product->id(),
                (string)$product->sku(),
                $title,
                $product->price()->amount(),
                $product->price()->currency(),
                $product->status()->value,
                $product->stock()
            );
            $this->em->persist($read);

            $created = new ProductCreatedEvent($product);
            $this->outbox->put($created);
            $this->dispatcher->dispatch($created);

            // Cache warm
            $this->cache->save($product);
            return $product;
        });
    }

    public function update(ProductInterface $product, ProductUpdateDTO $dto): ProductInterface
    {
        return $this->em->wrapInTransaction(function() use ($product, $dto) {
            $priceBefore = $product->price();
            if ($dto->price instanceof Money && !$dto->price->equals($priceBefore)) {
                if ($product instanceof Product) { $product->changePrice($dto->price); }
                $ev = new PriceChangedEvent($product, $priceBefore, $dto->price);
                $this->outbox->put($ev);
                $this->dispatcher->dispatch($ev);
            }

            if (is_int($dto->stockDelta) && $dto->stockDelta !== 0) {
                if ($product instanceof Product) { $product->adjustStock($dto->stockDelta); }
                $ev2 = new StockAdjustedEvent($product, $dto->stockDelta, $product->stock());
                $this->outbox->put($ev2);
                $this->dispatcher->dispatch($ev2);
            }

            $this->em->flush();

            // Refresh read snapshot (simple approach)
            $title = '—';
            $this->em->getConnection()->executeStatement('DELETE FROM product_read WHERE id = :id', ['id' => $product->id()]);
            $read = new ProductRead(
                $product->id(),
                (string)$product->sku(),
                $title,
                $product->price()->amount(),
                $product->price()->currency(),
                $product->status()->value,
                $product->stock()
            );
            $this->em->persist($read);

            $updated = new ProductUpdatedEvent($product);
            $this->outbox->put($updated);
            $this->dispatcher->dispatch($updated);

            $this->cache->save($product);
            return $product;
        });
    }
}
