<?php

declare(strict_types=1);

namespace App\Applicating\ServiceInterface;

use App\Applicating\DTO\Application\ApplicationManifestData;
use App\Applicating\DTO\Application\ApplicationReleaseData;
use App\Applicating\DTO\Application\ApplicationUpsertData;
use App\Applicating\DTO\Application\TenantApplicationAssignmentData;
use App\Applicating\Entity\Application;
use App\Applicating\Entity\ApplicationManifest;
use App\Applicating\Entity\ApplicationRelease;
use App\Applicating\Entity\TenantApplication;

interface ApplicationLifecycleServiceInterface
{
    public function createApplication(ApplicationUpsertData $data): Application;

    public function updateApplication(Application $application, ApplicationUpsertData $data): Application;

    public function createRelease(Application $application, ApplicationReleaseData $data): ApplicationRelease;

    public function publishApplication(Application $application, ApplicationRelease $release): void;

    public function suspendApplication(Application $application): void;

    public function createManifest(Application $application, ApplicationManifestData $data): ApplicationManifest;

    public function assignTenant(Application $application, TenantApplicationAssignmentData $data): TenantApplication;

    public function toggleTenantApplication(TenantApplication $tenantApplication, bool $enabled): void;
}
