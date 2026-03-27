<?php
declare(strict_types=1);

namespace App\Component\Product\Service;

use App\Component\Product\DTO\ProductCreateDTO;
use App\Component\Product\DTO\ProductUpdateDTO;
use App\Component\Product\Entity\Product;
use App\Component\Product\Entity\ProductI18n;
use App\Component\Product\Event\PriceChangedEvent;
use App\Component\Product\Event\ProductCreatedEvent;
use App\Component\Product\Event\ProductUpdatedEvent;
use App\Component\Product\Event\StockAdjustedEvent;
use App\Component\Product\ValueObject\Money;
use App\Component\Product\ValueObject\Sku;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

final class ProductService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EventDispatcherInterface $dispatcher
    ) {}

    public function create(ProductCreateDTO $dto): Product
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
        $this->em->persist($product);
        $this->em->flush();
        $this->dispatcher->dispatch(new ProductCreatedEvent($product));

        return $product;
    }

    public function update(Product $product, ProductUpdateDTO $dto): Product
    {
        $priceBefore = $product->price();
        $stockBefore = $product->stock();

        if ($dto->price instanceof Money && !$dto->price->equals($priceBefore)) {
            $product->changePrice($dto->price);
            $this->dispatcher->dispatch(new PriceChangedEvent($product, $priceBefore, $dto->price));
        }
        if (is_int($dto->stockDelta) && $dto->stockDelta !== 0) {
            $product->adjustStock($dto->stockDelta);
            $this->dispatcher->dispatch(new StockAdjustedEvent($product, $dto->stockDelta, $product->stock()));
        }

        $this->em->flush();
        $this->dispatcher->dispatch(new ProductUpdatedEvent($product));

        return $product;
    }
}
