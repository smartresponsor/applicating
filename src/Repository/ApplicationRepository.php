<?php

declare(strict_types=1);

namespace App\Applicating\Repository;

use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Enum\ApplicationPublicationState;
use App\Applicating\RepositoryInterface\ApplicationRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApplicationEntity>
 */
final class ApplicationRepository extends ServiceEntityRepository implements ApplicationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApplicationEntity::class);
    }

    public function findOneBySlug(string $slug): ?ApplicationEntity
    {
        $result = $this->createQueryBuilder('application')
            ->andWhere('application.objectIdentity.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();

        return $result instanceof ApplicationEntity ? $result : null;
    }

    /** @return list<ApplicationEntity> */
    public function findOrderedForAdmin(): array
    {
        /** @var list<ApplicationEntity> $result */
        $result = $this->createQueryBuilder('application')
            ->leftJoin('application.releases', 'release')->addSelect('release')
            ->leftJoin('application.tenantApplications', 'tenantApplication')->addSelect('tenantApplication')
            ->orderBy('application.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function countAllApplications(): int
    {
        return (int) $this->createQueryBuilder('application')
            ->select('COUNT(application.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPublished(): int
    {
        return (int) $this->createQueryBuilder('application')
            ->select('COUNT(application.id)')
            ->where('application.publicationState = :state')
            ->setParameter('state', ApplicationPublicationState::Published)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function save(ApplicationEntity $application): void
    {
        $this->getEntityManager()->persist($application);
        $this->getEntityManager()->flush();
    }
}
