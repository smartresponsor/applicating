<?php

declare(strict_types=1);

namespace App\Applicating\Repository;

use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Enum\ApplicationPublicationState;
use App\Applicating\RepositoryInterface\ApplicationRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Owns persistence and aggregate queries for the Applicating root application entity.
 *
 * @extends ServiceEntityRepository<ApplicationEntity>
 */
final class ApplicationRepository extends ServiceEntityRepository implements ApplicationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApplicationEntity::class);
    }

    /**
     * Resolve an application through the canonical Objecting slug identity.
     */
    public function findOneBySlug(string $slug): ?ApplicationEntity
    {
        $result = $this->createQueryBuilder('application')
            ->andWhere('application.objectIdentity.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();

        return $result instanceof ApplicationEntity ? $result : null;
    }

    /**
     * Return applications with admin-view relations ordered by latest modification.
     *
     * @return list<ApplicationEntity>
     */
    public function findOrderedForAdmin(): array
    {
        /** @var list<ApplicationEntity> $result */
        $result = $this->createQueryBuilder('application')
            ->leftJoin('application.releases', 'release')->addSelect('release')
            ->leftJoin('application.tenantApplications', 'tenantApplication')->addSelect('tenantApplication')
            ->orderBy('application.objectAudit.modifiedAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * Count all persisted applications regardless of publication state.
     */
    public function countAllApplications(): int
    {
        return (int) $this->createQueryBuilder('application')
            ->select('COUNT(application.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count applications currently in the published lifecycle state.
     */
    public function countPublished(): int
    {
        return (int) $this->createQueryBuilder('application')
            ->select('COUNT(application.id)')
            ->where('application.publicationState = :state')
            ->setParameter('state', ApplicationPublicationState::Published)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Persist an application aggregate and flush the unit of work immediately.
     */
    public function save(ApplicationEntity $application): void
    {
        $this->getEntityManager()->persist($application);
        $this->getEntityManager()->flush();
    }
}
