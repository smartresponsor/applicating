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
use App\Component\Product\ReadModel\Product\ProductRead;
use App\Component\Product\ValueObject\Product\Money;
use App\Component\Product\ValueObject\Product\Sku;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use App\Component\Product\Interface\Product\ProductCacheProviderInterface;

final class ProductService implements ProductServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly ProductRepositoryInterface $repo,
        private readonly ProductCacheProviderInterface $cache
    ) {}

    public function create(ProductCreateDTO $dto): ProductInterface
    {
        $product = new Product(new Sku($dto->sku), $dto->price, $dto->initialStock);
        foreach ($dto->i18n as $locale => $data) {
            $product->addTranslation(new ProductI18n(
                $product,
                $locale,
                $data['name'],
                $data['slug'],
                $data['description'] ?? null
            ));
        }

        $this->repo->save($product);

        // Create / sync read-model (simple approach)
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
        $this->em->flush();

        $this->dispatcher->dispatch(new ProductCreatedEvent($product));

        // Warm cache
        $this->cache->save($product);

        return $product;
    }

    public function update(ProductInterface $product, ProductUpdateDTO $dto): ProductInterface
    {
        $priceBefore = $product->price();
        $stockBefore = $product->stock();

        if ($dto->price instanceof Money && !$dto->price->equals($priceBefore)) {
            // force cast to entity type to call domain method
            if ($product instanceof Product) {
                $product->changePrice($dto->price);
            }
            $this->dispatcher->dispatch(new PriceChangedEvent($product, $priceBefore, $dto->price));
        }

        if (is_int($dto->stockDelta) && $dto->stockDelta !== 0) {
            if ($product instanceof Product) {
                $product->adjustStock($dto->stockDelta);
            }
            $this->dispatcher->dispatch(new StockAdjustedEvent($product, $dto->stockDelta, $product->stock()));
        }

        $this->em->flush();
        $this->dispatcher->dispatch(new ProductUpdatedEvent($product));

        // Sync read-model
        $read = $this->em->getRepository(ProductRead::class)->find($product->id());
        if ($read) {
            $this->em->remove($read); // keep simple: recreate snapshot
            $this->em->flush();
        }
        $title = '—'; // в реальном коде — вытянуть актуальный заголовок из i18n
        $newRead = new ProductRead(
            $product->id(),
            (string)$product->sku(),
            $title,
            $product->price()->amount(),
            $product->price()->currency(),
            $product->status()->value,
            $product->stock()
        );
        $this->em->persist($newRead);
        $this->em->flush();

        // Update cache
        $this->cache->save($product);

        return $product;
    }
}
