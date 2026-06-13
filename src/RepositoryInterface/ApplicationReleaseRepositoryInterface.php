<?php

declare(strict_types=1);

namespace App\Applicating\RepositoryInterface;

use App\Applicating\Entity\Application;
use App\Applicating\Entity\ApplicationRelease;

interface ApplicationReleaseRepositoryInterface
{
    public function findOneForApplicationAndVersion(Application $application, string $version): ?ApplicationRelease;
}
