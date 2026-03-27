<?php
declare(strict_types=1);

namespace App\Component\Product\Service\Product;

use App\Component\Product\Entity\Product\ProductCategory;
use Doctrine\ORM\EntityManagerInterface;

final class ProductCategoryService
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function create(string $slug, string $title, ?string $parentId = null): ProductCategory
    {
        $parent = $parentId ? $this->em->getRepository(ProductCategory::class)->find($parentId) : null;
        $cat = new ProductCategory($slug, $title, $parent);
        $this->em->persist($cat);
        $this->em->flush();
        return $cat;
    }
}
