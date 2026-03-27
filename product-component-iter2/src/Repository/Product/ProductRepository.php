<?php
declare(strict_types=1);

namespace App\Component\Product\Repository\Product;

use App\Component\Product\Interface\Product\ProductRepositoryInterface;
use App\Component\Product\Interface\Product\ProductInterface;
use App\Component\Product\Entity\Product\Product;
use Doctrine\ORM\EntityRepository;

final class ProductRepository extends EntityRepository implements ProductRepositoryInterface
{
    public function byId(string $id): ?ProductInterface
    {
        return $this->find($id);
    }

    public function bySku(string $sku): ?ProductInterface
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.product_sku_value = :sku')
            ->setParameter('sku', strtoupper($sku))
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(ProductInterface $product): void
    {
        $this->_em->persist($product);
        $this->_em->flush();
    }
}
