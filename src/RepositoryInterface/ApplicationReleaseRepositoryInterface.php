<?php

declare(strict_types=1);

namespace App\Applicating\RepositoryInterface;

use App\Applicating\Entity\ApplicationEntity;
use App\Applicating\Entity\ApplicationReleaseEntity;

interface ApplicationReleaseRepositoryInterface
{
    public function findOneForApplicationAndVersion(ApplicationEntity $application, string $version): ?ApplicationReleaseEntity;

    public function save(ApplicationReleaseEntity $release): void;
}
