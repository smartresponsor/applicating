<?php

declare(strict_types=1);

namespace App\Applicating\Service;

use App\Applicating\DTO\Application\ApplicationManifestData;
use App\Applicating\DTO\Application\ApplicationReleaseData;
use App\Applicating\DTO\Application\ApplicationUpsertData;
use App\Applicating\DTO\Application\TenantApplicationAssignmentData;
use App\Applicating\Entity\Application;
use App\Applicating\Entity\ApplicationManifest;
use App\Applicating\Entity\ApplicationRelease;
use App\Applicating\Entity\TenantApplication;
use App\Applicating\Enum\ApplicationAccessLevel;
use App\Applicating\Repository\ApplicationManifestRepository;
use App\Applicating\Repository\ApplicationReleaseRepository;
use App\Applicating\Repository\TenantApplicationRepository;
use App\Applicating\ServiceInterface\ApplicationDiagnosticsServiceInterface;
use App\Applicating\ServiceInterface\ApplicationLifecycleServiceInterface;
use App\Applicating\ServiceInterface\ApplicationManifestServiceInterface;
use App\Applicating\ValueObject\ApplicationManifestIdentifier;
use App\Applicating\ValueObject\ApplicationSlug;
use App\Applicating\ValueObject\ApplicationVersion;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ApplicationLifecycleService implements ApplicationLifecycleServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ApplicationManifestServiceInterface $applicationManifestService,
        private ApplicationDiagnosticsServiceInterface $applicationDiagnosticsService,
        private ApplicationReleaseRepository $applicationReleaseRepository,
        private ApplicationManifestRepository $applicationManifestRepository,
        private TenantApplicationRepository $tenantApplicationRepository,
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

    /**
     * @throws \JsonException
     */
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
