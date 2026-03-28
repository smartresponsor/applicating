<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TenantApplication;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TenantApplication>
 */
final class TenantApplicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TenantApplication::class);
    }

    /** @return list<TenantApplication> */
    public function findForTenant(string $tenantKey): array
    {
        /** @var list<TenantApplication> $result */
        $result = $this->createQueryBuilder('tenantApplication')
            ->leftJoin('tenantApplication.application', 'application')->addSelect('application')
            ->where('tenantApplication.tenantKey = :tenantKey')
            ->setParameter('tenantKey', $tenantKey)
            ->orderBy('tenantApplication.assignedAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $result;
    }
}
