<?php

declare(strict_types=1);

namespace App\Applicating\RepositoryInterface;

use App\Applicating\Entity\ApplicationEntity;
use App\Applicating\Entity\ApplicationManifestEntity;

interface ApplicationManifestRepositoryInterface
{
    public function findOneForApplicationAndIdentifier(ApplicationEntity $application, string $identifier): ?ApplicationManifestEntity;

    public function save(ApplicationManifestEntity $manifest): void;
}
