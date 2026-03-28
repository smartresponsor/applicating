<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ApplicationRelease;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApplicationRelease>
 */
final class ApplicationReleaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApplicationRelease::class);
    }
}
