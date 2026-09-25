<?php

declare(strict_types=1);

namespace App\Applicating\Repository;

use App\Applicating\Entity\ApplicationEntity;
use App\Applicating\Entity\ApplicationManifestEntity;
use App\Applicating\RepositoryInterface\ApplicationManifestRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApplicationManifestEntity>
 */
final class ApplicationManifestRepository extends ServiceEntityRepository implements ApplicationManifestRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApplicationManifestEntity::class);
    }

    public function findOneForApplicationAndIdentifier(ApplicationEntity $application, string $identifier): ?ApplicationManifestEntity
    {
        /** @var ApplicationManifestEntity|null $manifest */
        $manifest = $this->findOneBy([
            'application' => $application,
            'identifier' => $identifier,
        ]);

        return $manifest;
    }

    public function save(ApplicationManifestEntity $manifest): void
    {
        $this->getEntityManager()->persist($manifest);
        $this->getEntityManager()->flush();
    }
}
