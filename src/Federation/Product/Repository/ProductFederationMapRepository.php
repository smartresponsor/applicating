<?php

declare(strict_types=1);

namespace App\Federation\Product\Repository;

use App\Federation\Product\Entity\ProductFederationMap;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class ProductFederationMapRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $r)
    {
        parent::__construct($r, ProductFederationMap::class);
    }

    public function upsert(int $productId, string $fedId, string $tenantId, ?string $region, array $scope): ProductFederationMap
    {
        $em = $this->getEntityManager();
        $map = $this->findOneBy(['federationId' => $fedId]) ?? new ProductFederationMap($productId, $fedId, $tenantId, $region, $scope);
        if ($map->getProductId() !== $productId) {
            $rp = new \ReflectionProperty($map, 'productId');
            $rp->setAccessible(true);
            $rp->setValue($map, (string) $productId);
        } $map->touchSynced();
        $em->persist($map);
        $em->flush();

        return $map;
    }
}
