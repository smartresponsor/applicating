<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Application;
use App\Entity\ApplicationManifest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApplicationManifest>
 */
final class ApplicationManifestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApplicationManifest::class);
    }

    public function findOneForApplicationAndIdentifier(Application $application, string $identifier): ?ApplicationManifest
    {
        /** @var ApplicationManifest|null $manifest */
        $manifest = $this->findOneBy([
            'application' => $application,
            'identifier' => $identifier,
        ]);

        return $manifest;
    }
}
