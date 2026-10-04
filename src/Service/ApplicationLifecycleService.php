<?php

declare(strict_types=1);

namespace App\Applicating\Service;

use App\Applicating\DTO\ApplicationManifestDTO;
use App\Applicating\DTO\ApplicationReleaseDTO;
use App\Applicating\DTO\ApplicationTenantAssignmentDTO;
use App\Applicating\DTO\ApplicationUpsertDTO;
use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Entity\ApplicationManifestEntity;
use App\Applicating\Entity\ApplicationReleaseEntity;
use App\Applicating\Entity\ApplicationTenantAssignmentEntity;
use App\Applicating\Enum\ApplicationAccessLevel;
use App\Applicating\Repository\ApplicationManifestRepository;
use App\Applicating\Repository\ApplicationReleaseRepository;
use App\Applicating\Repository\ApplicationRepository;
use App\Applicating\Repository\ApplicationTenantAssignmentRepository;
use App\Applicating\ServiceInterface\ApplicationDiagnosticsServiceInterface;
use App\Applicating\ServiceInterface\ApplicationLifecycleServiceInterface;
use App\Applicating\ServiceInterface\ApplicationManifestServiceInterface;
use App\Applicating\ValueObject\ApplicationManifestIdentifier;
use App\Applicating\ValueObject\ApplicationSlug;
use App\Applicating\ValueObject\ApplicationVersion;

final readonly class ApplicationLifecycleService implements ApplicationLifecycleServiceInterface
{
    public function __construct(
        private ApplicationRepository $applicationRepository,
        private ApplicationManifestServiceInterface $applicationManifestService,
        private ApplicationDiagnosticsServiceInterface $applicationDiagnosticsService,
        private ApplicationReleaseRepository $applicationReleaseRepository,
        private ApplicationManifestRepository $applicationManifestRepository,
        private ApplicationTenantAssignmentRepository $tenantApplicationRepository,
    ) {
    }

    public function createApplication(ApplicationUpsertDTO $data): ApplicationEntity
    {
        $application = new ApplicationEntity(
            $data->nameEntity,
            (new ApplicationSlug($data->slug))->toString(),
            $data->packageName,
            $data->developerName,
            $data->listingSummary,
        );

        $this->applyApplicationData($application, $data);
        $this->applicationRepository->save($application);

        return $application;
    }

    public function updateApplication(ApplicationEntity $application, ApplicationUpsertDTO $data): ApplicationEntity
    {
        $application->rename($data->nameEntity);
        $application->changeSlug((new ApplicationSlug($data->slug))->toString());
        $application->changePackageName($data->packageName);
        $application->changeDeveloperName($data->developerName);
        $application->changeListingSummary($data->listingSummary);
        $this->applyApplicationData($application, $data);

        $this->applicationRepository->save($application);

        return $application;
    }

    public function createRelease(ApplicationEntity $application, ApplicationReleaseDTO $data): ApplicationReleaseEntity
    {
        $version = (new ApplicationVersion($data->version))->toString();
        $existingRelease = $this->applicationReleaseRepository->findOneForApplicationAndVersion($application, $version);

        if (null !== $existingRelease) {
            throw new \LogicException(sprintf('Release version %s already exists for application %s.', $version, $application->getSlug()));
        }

        $release = new ApplicationReleaseEntity(
            $application,
            $version,
            $data->channel,
            $data->checksum,
            $data->downloadUrl,
            $data->releaseNotes,
        );
        $application->addRelease($release);

        $this->applicationReleaseRepository->save($release);

        return $release;
    }

    public function publishApplication(ApplicationEntity $application, ApplicationReleaseEntity $release): void
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
        $this->applicationReleaseRepository->save($release);
    }

    public function suspendApplication(ApplicationEntity $application): void
    {
        $application->suspend();
        $this->applicationRepository->save($application);
    }

    public function createManifest(ApplicationEntity $application, ApplicationManifestDTO $data): ApplicationManifestEntity
    {
        $payload = $this->applicationManifestService->normalizeManifestPayload($data);
        $identifier = new ApplicationManifestIdentifier($payload['identifier']);
        $identifierValue = $identifier->toString();

        $existingManifest = $this->applicationManifestRepository->findOneForApplicationAndIdentifier($application, $identifierValue);
        if (null !== $existingManifest) {
            throw new \LogicException(sprintf('Manifest %s is already attached to application %s.', $identifierValue, $application->getSlug()));
        }

        $manifest = new ApplicationManifestEntity(
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

        $this->applicationManifestRepository->save($manifest);

        return $manifest;
    }

    /**
     * @throws \JsonException
     */
    public function assignTenant(ApplicationEntity $application, ApplicationTenantAssignmentDTO $data): ApplicationTenantAssignmentEntity
    {
        /** @var array<string, mixed> $policy */
        $policy = json_decode($data->accessPolicy, true, 512, JSON_THROW_ON_ERROR);
        $version = (new ApplicationVersion($data->installedVersion))->toString();

        $tenantApplication = $this->tenantApplicationRepository->findOneForTenantAndApplicationEntity($data->tenantKey, $application);

        if (null === $tenantApplication) {
            $tenantApplication = new ApplicationTenantAssignmentEntity(
                $application,
                $data->tenantKey,
                $version,
                $data->enabled,
                $data->billingActive,
                $policy,
            );
            $application->addTenantApplication($tenantApplication);
        } else {
            $tenantApplication->updateAssignment($version, $data->enabled, $data->billingActive, $policy);
        }

        $tenantApplication->setDiagnostics($this->applicationDiagnosticsService->buildTenantDiagnostics($tenantApplication)->toArray());
        $this->tenantApplicationRepository->save($tenantApplication);

        return $tenantApplication;
    }

    public function toggleTenantApplication(ApplicationTenantAssignmentEntity $tenantApplication, bool $enabled): void
    {
        if ($enabled) {
            $tenantApplication->enable();
        } else {
            $tenantApplication->disable();
        }

        $tenantApplication->setDiagnostics($this->applicationDiagnosticsService->buildTenantDiagnostics($tenantApplication)->toArray());
        $this->tenantApplicationRepository->save($tenantApplication);
    }

    private function applyApplicationData(ApplicationEntity $application, ApplicationUpsertDTO $data): void
    {
        $application->changeAccessLevel(ApplicationAccessLevel::from($data->accessLevel));
        $application->changeBillingCode($data->billingCode ?: null);
        $application->changeSandboxProfile($data->sandboxProfile);
        $application->changeEnabledByDefault($data->enabledByDefault);
    }
}
