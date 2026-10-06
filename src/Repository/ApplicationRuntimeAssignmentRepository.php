<?php

declare(strict_types=1);

namespace App\Applicating\Repository;

use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Entity\ApplicationRuntimeAssignmentEntity;
use App\Applicating\RepositoryInterface\ApplicationRuntimeAssignmentRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Persists environment-specific runtime assignments for application aggregates.
 *
 * @extends ServiceEntityRepository<ApplicationRuntimeAssignmentEntity>
 */
final class ApplicationRuntimeAssignmentRepository extends ServiceEntityRepository implements ApplicationRuntimeAssignmentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApplicationRuntimeAssignmentEntity::class);
    }

    public function findOneForApplicationAndEnvironment(string $applicationSlug, string $environment): ?ApplicationRuntimeAssignmentEntity
    {
        /** @var ApplicationRuntimeAssignmentEntity|null $assignment */
        $assignment = $this->createQueryBuilder('runtimeAssignment')
            ->innerJoin('runtimeAssignment.application', 'application')
            ->addSelect('application')
            ->where('application.objectIdentity.slug = :applicationSlug')
            ->andWhere('runtimeAssignment.environment = :environment')
            ->setParameter('applicationSlug', trim($applicationSlug))
            ->setParameter('environment', trim($environment))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $assignment;
    }

    public function findOneForApplicationEntityAndEnvironment(ApplicationEntity $application, string $environment): ?ApplicationRuntimeAssignmentEntity
    {
        $assignment = $this->findOneBy([
            'application' => $application,
            'environment' => trim($environment),
        ]);

        return $assignment instanceof ApplicationRuntimeAssignmentEntity ? $assignment : null;
    }

    public function save(ApplicationRuntimeAssignmentEntity $assignment): void
    {
        $this->getEntityManager()->persist($assignment);
        $this->getEntityManager()->flush();
    }
}
