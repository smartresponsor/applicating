<?php

declare(strict_types=1);

namespace App\Applicating\ServiceInterface;

use App\Applicating\DTO\ApplicationManifestDTO;
use App\Applicating\DTO\ApplicationReleaseDTO;
use App\Applicating\DTO\ApplicationTenantAssignmentDTO;
use App\Applicating\DTO\ApplicationUpsertDTO;
use App\Applicating\Entity\ApplicationEntity;
use App\Applicating\Entity\ApplicationManifestEntity;
use App\Applicating\Entity\ApplicationReleaseEntity;
use App\Applicating\Entity\ApplicationTenantAssignmentEntity;

interface ApplicationLifecycleServiceInterface
{
    public function createApplication(ApplicationUpsertDTO $data): ApplicationEntity;

    public function updateApplication(ApplicationEntity $application, ApplicationUpsertDTO $data): ApplicationEntity;

    public function createRelease(ApplicationEntity $application, ApplicationReleaseDTO $data): ApplicationReleaseEntity;

    public function publishApplication(ApplicationEntity $application, ApplicationReleaseEntity $release): void;

    public function suspendApplication(ApplicationEntity $application): void;

    public function createManifest(ApplicationEntity $application, ApplicationManifestDTO $data): ApplicationManifestEntity;

    public function assignTenant(ApplicationEntity $application, ApplicationTenantAssignmentDTO $data): ApplicationTenantAssignmentEntity;

    public function toggleTenantApplication(ApplicationTenantAssignmentEntity $tenantApplication, bool $enabled): void;
}
