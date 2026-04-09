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
use App\Repository\ApplicationManifestRepository;
use App\Repository\ApplicationReleaseRepository;
use App\Repository\TenantApplicationRepository;
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
        private readonly ApplicationReleaseRepository $applicationReleaseRepository,
        private readonly ApplicationManifestRepository $applicationManifestRepository,
        private readonly TenantApplicationRepository $tenantApplicationRepository,
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
        $version = (new ApplicationVersion($data->version))->toString();
        $existingRelease = $this->applicationReleaseRepository->findOneForApplicationAndVersion($application, $version);

        if (null !== $existingRelease) {
            throw new \LogicException(sprintf('Release version %s already exists for application %s.', $version, $application->getSlug()));
        }

        $release = new ApplicationRelease(
            $application,
            $version,
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
        if ($release->getApplication() !== $application) {
            throw new \LogicException('Application release does not belong to the selected application.');
        }

        if (0 === $application->getManifests()->count()) {
            throw new \LogicException('Application cannot be published without a manifest.');
        }

        $approvedManifestPresent = false;
        foreach ($application->getManifests() as $manifest) {
            if ('approved' === $manifest->getGovernanceState()) {
                $approvedManifestPresent = true;
                break;
            }
        }

        if (!$approvedManifestPresent) {
            throw new \LogicException('Application cannot be published without an approved manifest.');
        }

        if ('published' === $release->getPublicationState()->value) {
            throw new \LogicException('Application release is already published.');
        }

        $application->markForModeration();
        $application->publish();
        $release->publish();
        $this->entityManager->flush();
    }

    public function suspendApplication(Application $application): void
    {
        $application->suspend();
        $this->entityManager->flush();
    }

    public function createManifest(Application $application, ApplicationManifestData $data): ApplicationManifest
    {
        $payload = $this->applicationManifestService->normalizeManifestPayload($data);
        $identifier = new ApplicationManifestIdentifier($payload['identifier']);
        $identifierValue = $identifier->toString();

        $existingManifest = $this->applicationManifestRepository->findOneForApplicationAndIdentifier($application, $identifierValue);
        if (null !== $existingManifest) {
            throw new \LogicException(sprintf('Manifest %s is already attached to application %s.', $identifierValue, $application->getSlug()));
        }

        $manifest = new ApplicationManifest(
            $application,
            $payload['manifestVersion'],
            $identifierValue,
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
        $version = (new ApplicationVersion($data->installedVersion))->toString();

        $tenantApplication = $this->tenantApplicationRepository->findOneForTenantAndApplicationEntity($data->tenantKey, $application);

        if (null === $tenantApplication) {
            $tenantApplication = new TenantApplication(
                $application,
                $data->tenantKey,
                $version,
                $data->enabled,
                $data->billingActive,
                $policy,
            );
            $application->addTenantApplication($tenantApplication);
            $this->entityManager->persist($tenantApplication);
        } else {
            $tenantApplication->updateAssignment($version, $data->enabled, $data->billingActive, $policy);
        }

        $tenantApplication->setDiagnostics($this->applicationDiagnosticsService->buildTenantDiagnostics($tenantApplication)->toArray());
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

        $tenantApplication->setDiagnostics($this->applicationDiagnosticsService->buildTenantDiagnostics($tenantApplication)->toArray());
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
