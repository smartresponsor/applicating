<?php

declare(strict_types=1);

namespace App\Application\ServiceInterface;

use App\Application\DTO\Application\ApplicationManifestData;
use App\Application\DTO\Application\ApplicationReleaseData;
use App\Application\DTO\Application\ApplicationUpsertData;
use App\Application\DTO\Application\TenantApplicationAssignmentData;
use App\Application\Entity\Application;
use App\Application\Entity\ApplicationManifest;
use App\Application\Entity\ApplicationRelease;
use App\Application\Entity\TenantApplication;

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
