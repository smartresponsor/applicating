<?php
declare(strict_types=1);

namespace App\Component\Product\Factory\Product;

use App\Component\Product\DTO\Product\ProductCreateDTO;
use App\Component\Product\Entity\Product\Product;
use App\Component\Product\Entity\Product\ProductI18n;
use App\Component\Product\Enum\ProductStatus;
use App\Component\Product\ValueObject\Product\Sku;

final class ProductFactory
{
    public function fromDTO(ProductCreateDTO $dto): Product
    {
        $p = new Product(new Sku($dto->sku), $dto->price, $dto->initialStock, ProductStatus::DRAFT);
        foreach ($dto->i18n as $locale => $data) {
            $p->addTranslation(new ProductI18n($p, $locale, $data['name'], $data['slug'], $data['description'] ?? null));
        }
        return $p;
    }
}
