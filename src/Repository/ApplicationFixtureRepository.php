<?php

declare(strict_types=1);

namespace App\Applicating\Repository;

use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Entity\ApplicationManifestEntity;
use App\Applicating\Entity\ApplicationReleaseEntity;
use App\Applicating\Entity\ApplicationRuntimeAssignmentEntity;
use App\Applicating\Entity\ApplicationTenantAssignmentEntity;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Owns persistence-only fixture and demo-reset operations for Applicating.
 */
final readonly class ApplicationFixtureRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param array<FixtureInterface> $fixtures
     */
    public function loadFixtures(array $fixtures): void
    {
        $executor = new ORMExecutor($this->entityManager, new ORMPurger());
        $executor->execute($fixtures, true);
    }

    public function resetDemoData(): void
    {
        foreach ([
            ApplicationTenantAssignmentEntity::class,
            ApplicationRuntimeAssignmentEntity::class,
            ApplicationManifestEntity::class,
            ApplicationReleaseEntity::class,
            ApplicationEntity::class,
        ] as $entityClass) {
            $this->entityManager->createQuery(sprintf('DELETE FROM %s entity', $entityClass))->execute();
        }
    }
}
