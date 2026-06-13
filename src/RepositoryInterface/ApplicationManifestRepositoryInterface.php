<?php

declare(strict_types=1);

namespace App\Applicating\RepositoryInterface;

use App\Applicating\Entity\Application;
use App\Applicating\Entity\ApplicationManifest;

interface ApplicationManifestRepositoryInterface
{
    public function findOneForApplicationAndIdentifier(Application $application, string $identifier): ?ApplicationManifest;
}
