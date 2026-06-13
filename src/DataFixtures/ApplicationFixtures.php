<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\DataFixtures;

use App\Applicating\DTO\Application\ApplicationManifestData;
use App\Applicating\DTO\Application\ApplicationReleaseData;
use App\Applicating\DTO\Application\ApplicationUpsertData;
use App\Applicating\DTO\Application\TenantApplicationAssignmentData;
use App\Applicating\ServiceInterface\ApplicationLifecycleServiceInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class ApplicationFixtures extends Fixture
{
    public function __construct(private readonly ApplicationLifecycleServiceInterface $applicationLifecycleService)
    {
    }

    /**
     * @throws \JsonException
     */
    public function load(ObjectManager $manager): void
    {
        $developers = ['Atlas Works', 'Northwind Labs', 'Summit Studio', 'Orbit Systems', 'Polar Forge', 'Zenith House'];
        $summaries = [
            'Deterministic demo listing for the host bootstrap.',
            'Catalog of reproducible application demo content.',
            'Host-facing sample app metadata for Interfacing screens.',
            'Stable listing data for local validation and demos.',
            'Demo application payload used by the fixture layer.',
            'Synthetic but business-shaped application metadata.',
        ];
        $notes = [
            'Initial release notes for the first demo application.',
            'Beta rollout notes for the second demo application.',
            'Stable release notes for the third demo application.',
            'Maintenance release notes for the fourth demo application.',
            'Review-bound release notes for the fifth demo application.',
            'Sandbox release notes for the sixth demo application.',
        ];

        for ($index = 1; $index <= 6; ++$index) {
            $applicationData = new ApplicationUpsertData();
            $applicationData->nameEntity = sprintf('Demo Application %02d Suite', $index);
            $applicationData->slug = sprintf('application-%d', $index);
            $applicationData->packageName = sprintf('applicating/demo-application-%d', $index);
            $applicationData->developerName = $developers[$index - 1];
            $applicationData->listingSummary = $summaries[$index - 1];
            $applicationData->accessLevel = 0 === $index % 2 ? 'public' : 'tenant_restricted';
            $applicationData->billingCode = sprintf('APP-%03d', $index);
            $applicationData->sandboxProfile = 0 === $index % 3 ? 'restricted' : 'default';
            $applicationData->enabledByDefault = 0 === $index % 2;
            $application = $this->applicationLifecycleService->createApplication($applicationData);

            $releaseData = new ApplicationReleaseData();
            $releaseData->version = sprintf('1.%d.0', $index);
            $releaseData->channel = 0 === $index % 2 ? 'stable' : 'beta';
            $releaseData->checksum = hash('sha256', $application->getSlug().$index);
            $releaseData->downloadUrl = sprintf('https://downloads.example.test/%s/%s.zip', $application->getSlug(), $releaseData->version);
            $releaseData->releaseNotes = $notes[$index - 1];
            $release = $this->applicationLifecycleService->createRelease($application, $releaseData);

            $manifestData = new ApplicationManifestData();
            $manifestData->identifier = sprintf('io.applicating.%s', str_replace('-', '.', $application->getSlug()));
            $manifestData->capabilities = "listing\nreporting\nbilling-hook";
            $manifestData->permissions = "tenant:read\ntenant:write";
            $manifestData->runtimeHooks = "bootstrap\npost_install";
            $manifestData->sandboxProfile = $application->getSandboxProfile();
            $manifestData->governanceState = $index <= 4 ? 'approved' : 'review_required';
            $this->applicationLifecycleService->createManifest($application, $manifestData);

            if ($index <= 4) {
                $this->applicationLifecycleService->publishApplication($application, $release);
            }

            $assignmentData = new TenantApplicationAssignmentData();
            $assignmentData->tenantKey = sprintf('tenant-%02d', $index);
            $assignmentData->installedVersion = $releaseData->version;
            $assignmentData->enabled = 0 !== $index % 3;
            $assignmentData->billingActive = 0 !== $index % 2;
            $assignmentData->accessPolicy = json_encode([
                'scope' => 'tenant',
                'roles' => ['ROLE_APPLICATION_USER', 'ROLE_APPLICATION_ADMIN'],
                'moderation' => $manifestData->governanceState,
            ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
            $this->applicationLifecycleService->assignTenant($application, $assignmentData);
        }
    }
}
