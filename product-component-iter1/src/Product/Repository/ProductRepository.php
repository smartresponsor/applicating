<?php
declare(strict_types=1);

namespace App\Component\Product\Repository;

use App\Component\Product\Entity\Product;
use Doctrine\ORM\EntityRepository;

final class ProductRepository extends EntityRepository
{
    public function findOneBySku(string $sku): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.sku_value = :sku') /* Embedded column with prefix sku_ + property 'value' */
            ->setParameter('sku', strtoupper($sku))
            ->getQuery()
            ->getOneOrNullResult();
    }
}
