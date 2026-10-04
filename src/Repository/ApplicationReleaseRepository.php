<?php

declare(strict_types=1);

namespace App\Applicating\Repository;

use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Entity\ApplicationReleaseEntity;
use App\Applicating\RepositoryInterface\ApplicationReleaseRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApplicationReleaseEntity>
 */
final class ApplicationReleaseRepository extends ServiceEntityRepository implements ApplicationReleaseRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApplicationReleaseEntity::class);
    }

    public function findOneForApplicationAndVersion(ApplicationEntity $application, string $version): ?ApplicationReleaseEntity
    {
        /** @var ApplicationReleaseEntity|null $release */
        $release = $this->findOneBy([
            'application' => $application,
            'version' => $version,
        ]);

        return $release;
    }

    public function save(ApplicationReleaseEntity $release): void
    {
        $this->getEntityManager()->persist($release);
        $this->getEntityManager()->flush();
    }
}
