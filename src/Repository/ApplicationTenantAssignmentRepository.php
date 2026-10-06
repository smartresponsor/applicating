<?php

declare(strict_types=1);

namespace App\Applicating\Repository;

use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Entity\ApplicationTenantAssignmentEntity;
use App\Applicating\RepositoryInterface\ApplicationTenantAssignmentRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Persists and queries tenant-to-application lifecycle assignments for Applicating.
 *
 * @extends ServiceEntityRepository<ApplicationTenantAssignmentEntity>
 */
final class ApplicationTenantAssignmentRepository extends ServiceEntityRepository implements ApplicationTenantAssignmentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApplicationTenantAssignmentEntity::class);
    }

    /**
     * Return assignments for one tenant ordered from newest to oldest.
     *
     * @return list<ApplicationTenantAssignmentEntity>
     */
    public function findForTenant(string $tenantKey): array
    {
        /** @var list<ApplicationTenantAssignmentEntity> $result */
        $result = $this->createQueryBuilder('tenantApplication')
            ->leftJoin('tenantApplication.application', 'application')->addSelect('application')
            ->where('tenantApplication.tenantKey = :tenantKey')
            ->setParameter('tenantKey', $tenantKey)
            ->orderBy('tenantApplication.assignedAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * Resolve one tenant assignment by tenant key and application slug.
     */
    public function findOneForTenantAndApplication(string $tenantKey, string $applicationSlug): ?ApplicationTenantAssignmentEntity
    {
        /** @var ApplicationTenantAssignmentEntity|null $tenantApplication */
        $tenantApplication = $this->createQueryBuilder('tenantApplication')
            ->leftJoin('tenantApplication.application', 'application')->addSelect('application')
            ->where('tenantApplication.tenantKey = :tenantKey')
            ->andWhere('application.objectIdentity.slug = :applicationSlug')
            ->setParameter('tenantKey', $tenantKey)
            ->setParameter('applicationSlug', $applicationSlug)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $tenantApplication;
    }

    /**
     * Resolve one tenant assignment by tenant key and application entity identity.
     */
    public function findOneForTenantAndApplicationEntity(string $tenantKey, ApplicationEntity $application): ?ApplicationTenantAssignmentEntity
    {
        /** @var ApplicationTenantAssignmentEntity|null $tenantApplication */
        $tenantApplication = $this->findOneBy([
            'tenantKey' => $tenantKey,
            'application' => $application,
        ]);

        return $tenantApplication;
    }

    /**
     * Count every persisted application assignment across all tenants.
     */
    public function countAllAssignments(): int
    {
        return (int) $this->createQueryBuilder('tenantApplication')
            ->select('COUNT(tenantApplication.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count assignments currently enabled for tenant access.
     */
    public function countEnabledAssignments(): int
    {
        return (int) $this->createQueryBuilder('tenantApplication')
            ->select('COUNT(tenantApplication.id)')
            ->where('tenantApplication.enabled = :enabled')
            ->setParameter('enabled', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count assignments whose billing lifecycle is currently active.
     */
    public function countBillingActiveAssignments(): int
    {
        return (int) $this->createQueryBuilder('tenantApplication')
            ->select('COUNT(tenantApplication.id)')
            ->where('tenantApplication.billingActive = :billingActive')
            ->setParameter('billingActive', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Persist an assignment and flush the unit of work immediately.
     */
    public function save(ApplicationTenantAssignmentEntity $assignment): void
    {
        $this->getEntityManager()->persist($assignment);
        $this->getEntityManager()->flush();
    }
}
