<?php

declare(strict_types=1);

namespace App\Applicating\ServiceInterface;

use App\Applicating\DTO\ApplicationManifestDTO;
use App\Applicating\DTO\ApplicationReleaseDTO;
use App\Applicating\DTO\ApplicationUpsertDTO;
use App\Applicating\DTO\TenantApplicationAssignmentDTO;
use App\Applicating\Entity\Application;
use App\Applicating\Entity\ApplicationManifest;
use App\Applicating\Entity\ApplicationRelease;
use App\Applicating\Entity\TenantApplication;

interface ApplicationLifecycleServiceInterface
{
    public function createApplication(ApplicationUpsertDTO $data): Application;

    public function updateApplication(Application $application, ApplicationUpsertDTO $data): Application;

    public function createRelease(Application $application, ApplicationReleaseDTO $data): ApplicationRelease;

    public function publishApplication(Application $application, ApplicationRelease $release): void;

    public function suspendApplication(Application $application): void;

    public function createManifest(Application $application, ApplicationManifestDTO $data): ApplicationManifest;

    public function assignTenant(Application $application, TenantApplicationAssignmentDTO $data): TenantApplication;

    public function toggleTenantApplication(TenantApplication $tenantApplication, bool $enabled): void;
}
