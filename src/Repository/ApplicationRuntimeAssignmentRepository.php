<?php

declare(strict_types=1);

namespace App\Applicating\Repository;

use App\Applicating\Entity\Application;
use App\Applicating\Entity\ApplicationRuntimeAssignment;
use App\Applicating\RepositoryInterface\ApplicationRuntimeAssignmentRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<ApplicationRuntimeAssignment> */
final class ApplicationRuntimeAssignmentRepository extends ServiceEntityRepository implements ApplicationRuntimeAssignmentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApplicationRuntimeAssignment::class);
    }

    public function findOneForApplicationAndEnvironment(string $applicationSlug, string $environment): ?ApplicationRuntimeAssignment
    {
        /** @var ApplicationRuntimeAssignment|null $assignment */
        $assignment = $this->createQueryBuilder('runtimeAssignment')
            ->innerJoin('runtimeAssignment.application', 'application')
            ->addSelect('application')
            ->where('application.slug = :applicationSlug')
            ->andWhere('runtimeAssignment.environment = :environment')
            ->setParameter('applicationSlug', trim($applicationSlug))
            ->setParameter('environment', trim($environment))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $assignment;
    }

    public function findOneForApplicationEntityAndEnvironment(Application $application, string $environment): ?ApplicationRuntimeAssignment
    {
        $assignment = $this->findOneBy([
            'application' => $application,
            'environment' => trim($environment),
        ]);

        return $assignment instanceof ApplicationRuntimeAssignment ? $assignment : null;
    }
}
