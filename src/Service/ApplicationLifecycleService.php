<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Application\ApplicationManifestData;
use App\DTO\Application\ApplicationReleaseData;
use App\DTO\Application\ApplicationUpsertData;
use App\DTO\Application\TenantApplicationAssignmentData;
use App\Entity\Application;
use App\Entity\ApplicationManifest;
use App\Entity\ApplicationRelease;
use App\Entity\TenantApplication;
use App\Enum\ApplicationAccessLevel;
use App\ServiceInterface\ApplicationDiagnosticsServiceInterface;
use App\ServiceInterface\ApplicationLifecycleServiceInterface;
use App\ServiceInterface\ApplicationManifestServiceInterface;
use App\ValueObject\ApplicationManifestIdentifier;
use App\ValueObject\ApplicationSlug;
use App\ValueObject\ApplicationVersion;
use Doctrine\ORM\EntityManagerInterface;

final class ApplicationLifecycleService implements ApplicationLifecycleServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ApplicationManifestServiceInterface $applicationManifestService,
        private readonly ApplicationDiagnosticsServiceInterface $applicationDiagnosticsService,
    ) {
    }

    public function createApplication(ApplicationUpsertData $data): Application
    {
        $application = new Application(
            $data->name,
            (new ApplicationSlug($data->slug))->toString(),
            $data->packageName,
            $data->developerName,
            $data->listingSummary,
        );

        $this->applyApplicationData($application, $data);
        $this->entityManager->persist($application);
        $this->entityManager->flush();

        return $application;
    }

    public function updateApplication(Application $application, ApplicationUpsertData $data): Application
    {
        $application->rename($data->name);
        $application->changeSlug((new ApplicationSlug($data->slug))->toString());
        $application->changePackageName($data->packageName);
        $application->changeDeveloperName($data->developerName);
        $application->changeListingSummary($data->listingSummary);
        $this->applyApplicationData($application, $data);

        $this->entityManager->flush();

        return $application;
    }

    public function createRelease(Application $application, ApplicationReleaseData $data): ApplicationRelease
    {
        $release = new ApplicationRelease(
            $application,
            (new ApplicationVersion($data->version))->toString(),
            $data->channel,
            $data->checksum,
            $data->downloadUrl,
            $data->releaseNotes,
        );
        $application->addRelease($release);

        $this->entityManager->persist($release);
        $this->entityManager->flush();

        return $release;
    }

    public function publishApplication(Application $application, ApplicationRelease $release): void
    {
        $application->markForModeration();
        $application->publish();
        $release->publish();
        $this->entityManager->flush();
    }

    public function createManifest(Application $application, ApplicationManifestData $data): ApplicationManifest
    {
        $payload = $this->applicationManifestService->normalizeManifestPayload($data);
        $identifier = new ApplicationManifestIdentifier($payload['identifier']);

        $manifest = new ApplicationManifest(
            $application,
            $payload['manifestVersion'],
            $identifier->toString(),
            $payload['capabilities'],
            $payload['permissions'],
            $payload['runtimeHooks'],
            $payload['sandboxProfile'],
            $payload['governanceState'],
            $payload,
        );
        $application->addManifest($manifest);

        $this->entityManager->persist($manifest);
        $this->entityManager->flush();

        return $manifest;
    }

    public function assignTenant(Application $application, TenantApplicationAssignmentData $data): TenantApplication
    {
        /** @var array<string, mixed> $policy */
        $policy = json_decode($data->accessPolicy, true, 512, JSON_THROW_ON_ERROR);
        $tenantApplication = new TenantApplication(
            $application,
            $data->tenantKey,
            (new ApplicationVersion($data->installedVersion))->toString(),
            $data->enabled,
            $data->billingActive,
            $policy,
        );
        $tenantApplication->setDiagnostics($this->applicationDiagnosticsService->buildTenantDiagnostics($tenantApplication));
        $application->addTenantApplication($tenantApplication);

        $this->entityManager->persist($tenantApplication);
        $this->entityManager->flush();

        return $tenantApplication;
    }

    public function toggleTenantApplication(TenantApplication $tenantApplication, bool $enabled): void
    {
        if ($enabled) {
            $tenantApplication->enable();
        } else {
            $tenantApplication->disable();
        }

        $tenantApplication->setDiagnostics($this->applicationDiagnosticsService->buildTenantDiagnostics($tenantApplication));
        $this->entityManager->flush();
    }

    private function applyApplicationData(Application $application, ApplicationUpsertData $data): void
    {
        $application->changeAccessLevel(ApplicationAccessLevel::from($data->accessLevel));
        $application->changeBillingCode($data->billingCode ?: null);
        $application->changeSandboxProfile($data->sandboxProfile);
        $application->changeEnabledByDefault($data->enabledByDefault);
    }
}
