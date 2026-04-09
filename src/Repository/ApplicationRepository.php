<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Application;
use App\Enum\ApplicationPublicationState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Application>
 */
final class ApplicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Application::class);
    }

    /** @return list<Application> */
    public function findOrderedForAdmin(): array
    {
        /** @var list<Application> $result */
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
}
