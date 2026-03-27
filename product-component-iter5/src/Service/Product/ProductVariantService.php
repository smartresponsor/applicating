<?php
declare(strict_types=1);

namespace App\Component\Product\Service\Product;

use App\Component\Product\DTO\Product\ProductVariantCreateDTO;
use App\Component\Product\Entity\Product\Product;
use App\Component\Product\Entity\Product\ProductVariant;
use App\Component\Product\Entity\Product\ProductOption;
use App\Component\Product\Entity\Product\ProductOptionValue;
use Doctrine\ORM\EntityManagerInterface;

final class ProductVariantService
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function create(ProductVariantCreateDTO $dto): ProductVariant
    {
        $product = $this->em->getRepository(Product::class)->find($dto->productId);
        if (!$product) {
            throw new \RuntimeException('Product not found');
        }
        $variant = new ProductVariant($product, $dto->variantSku, $dto->priceAmount, $dto->priceCurrency, $dto->stock);
        $this->em->persist($variant);

        foreach ($dto->options as $code => $value) {
            $option = new ProductOption($product, (string)$code);
            $this->em->persist($option);
            $this->em->persist(new ProductOptionValue($option, (string)$value));
        }

        $this->em->flush();
        return $variant;
    }
}
