<?php

declare(strict_types=1);

namespace App\Applicating\Repository;

use App\Applicating\Entity\Application;
use App\Applicating\Entity\ApplicationRelease;
use App\Applicating\RepositoryInterface\ApplicationReleaseRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApplicationRelease>
 */
final class ApplicationReleaseRepository extends ServiceEntityRepository implements ApplicationReleaseRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApplicationRelease::class);
    }

    public function findOneForApplicationAndVersion(Application $application, string $version): ?ApplicationRelease
    {
        /** @var ApplicationRelease|null $release */
        $release = $this->findOneBy([
            'application' => $application,
            'version' => $version,
        ]);

        return $release;
    }
}
