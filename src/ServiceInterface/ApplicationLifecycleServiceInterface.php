<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\DTO\Application\ApplicationManifestData;
use App\DTO\Application\ApplicationReleaseData;
use App\DTO\Application\ApplicationUpsertData;
use App\DTO\Application\TenantApplicationAssignmentData;
use App\Entity\Application;
use App\Entity\ApplicationManifest;
use App\Entity\ApplicationRelease;
use App\Entity\TenantApplication;

interface ApplicationLifecycleServiceInterface
{
    public function createApplication(ApplicationUpsertData $data): Application;

    public function updateApplication(Application $application, ApplicationUpsertData $data): Application;

    public function createRelease(Application $application, ApplicationReleaseData $data): ApplicationRelease;

    public function publishApplication(Application $application, ApplicationRelease $release): void;

    public function createManifest(Application $application, ApplicationManifestData $data): ApplicationManifest;

    public function assignTenant(Application $application, TenantApplicationAssignmentData $data): TenantApplication;

    public function toggleTenantApplication(TenantApplication $tenantApplication, bool $enabled): void;
}
