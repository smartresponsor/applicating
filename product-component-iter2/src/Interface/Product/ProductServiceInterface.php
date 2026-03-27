<?php
declare(strict_types=1);

namespace App\Component\Product\Interface\Product;

use App\Component\Product\DTO\Product\ProductCreateDTO;
use App\Component\Product\DTO\Product\ProductUpdateDTO;

interface ProductServiceInterface
{
    public function create(ProductCreateDTO $dto): ProductInterface;
    public function update(ProductInterface $product, ProductUpdateDTO $dto): ProductInterface;
}
