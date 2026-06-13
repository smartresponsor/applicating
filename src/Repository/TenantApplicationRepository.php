<?php

declare(strict_types=1);

namespace App\Applicating\Repository;

use App\Applicating\Entity\Application;
use App\Applicating\Entity\TenantApplication;
use App\Applicating\RepositoryInterface\TenantApplicationRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TenantApplication>
 */
final class TenantApplicationRepository extends ServiceEntityRepository implements TenantApplicationRepositoryInterface
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

    public function findOneForTenantAndApplication(string $tenantKey, string $applicationSlug): ?TenantApplication
    {
        /** @var TenantApplication|null $tenantApplication */
        $tenantApplication = $this->createQueryBuilder('tenantApplication')
            ->leftJoin('tenantApplication.application', 'application')->addSelect('application')
            ->where('tenantApplication.tenantKey = :tenantKey')
            ->andWhere('application.slug = :applicationSlug')
            ->setParameter('tenantKey', $tenantKey)
            ->setParameter('applicationSlug', $applicationSlug)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $tenantApplication;
    }

    public function findOneForTenantAndApplicationEntity(string $tenantKey, Application $application): ?TenantApplication
    {
        /** @var TenantApplication|null $tenantApplication */
        $tenantApplication = $this->findOneBy([
            'tenantKey' => $tenantKey,
            'application' => $application,
        ]);

        return $tenantApplication;
    }

    public function countAllAssignments(): int
    {
        return (int) $this->createQueryBuilder('tenantApplication')
            ->select('COUNT(tenantApplication.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countEnabledAssignments(): int
    {
        return (int) $this->createQueryBuilder('tenantApplication')
            ->select('COUNT(tenantApplication.id)')
            ->where('tenantApplication.enabled = :enabled')
            ->setParameter('enabled', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countBillingActiveAssignments(): int
    {
        return (int) $this->createQueryBuilder('tenantApplication')
            ->select('COUNT(tenantApplication.id)')
            ->where('tenantApplication.billingActive = :billingActive')
            ->setParameter('billingActive', true)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
