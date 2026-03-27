<?php

declare(strict_types=1);

namespace App\Federation\Product\Repository;

use App\Federation\Product\Entity\FederationRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class FederationRegistryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $r)
    {
        parent::__construct($r, FederationRegistry::class);
    }

    public function upsert(string $nodeId, string $baseUrl, ?string $publicKey, ?string $region, array $meta = []): FederationRegistry
    {
        $em = $this->getEntityManager();
        $node = $this->findOneBy(['nodeId' => $nodeId]) ?? new FederationRegistry($nodeId, $baseUrl, $publicKey, $region, $meta);
        $em->persist($node);
        $em->flush();

        return $node;
    }
}
