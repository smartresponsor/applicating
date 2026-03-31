<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\DTO\Application\ApplicationManifestData;
use App\DTO\Application\ApplicationReleaseData;
use App\DTO\Application\ApplicationUpsertData;
use App\DTO\Application\TenantApplicationAssignmentData;
use App\ServiceInterface\ApplicationLifecycleServiceInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

final class ApplicationFixtures extends Fixture
{
    public function __construct(private readonly ApplicationLifecycleServiceInterface $applicationLifecycleService)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($index = 1; $index <= 6; ++$index) {
            $applicationData = new ApplicationUpsertData();
            /** @var list<string> $words */
            $words = $faker->unique()->words(2);
            $applicationData->name = ucfirst(implode(' ', $words)).' Suite';
            $applicationData->slug = sprintf('application-%d', $index);
            $applicationData->packageName = sprintf('applicating/demo-application-%d', $index);
            $applicationData->developerName = $faker->company();
            $applicationData->listingSummary = $faker->sentence(14);
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
            $releaseData->releaseNotes = $faker->paragraph();
            $release = $this->applicationLifecycleService->createRelease($application, $releaseData);

            $manifestData = new ApplicationManifestData();
            $manifestData->identifier = sprintf('io.applicating.%s', str_replace('-', '.', $application->getSlug()));
            $manifestData->capabilities = "catalog\nreporting\nbilling-hook";
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
