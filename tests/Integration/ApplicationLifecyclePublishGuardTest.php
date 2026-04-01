<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\DTO\Application\ApplicationManifestData;
use App\DTO\Application\ApplicationReleaseData;
use App\DTO\Application\ApplicationUpsertData;
use App\Entity\Application;
use App\Entity\ApplicationManifest;
use App\Entity\ApplicationRelease;
use App\ServiceInterface\ApplicationLifecycleServiceInterface;
use App\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ApplicationLifecyclePublishGuardTest extends KernelTestCase
{
    private ApplicationLifecycleServiceInterface $applicationLifecycleService;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        /** @var ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();
        DoctrineSchemaResetter::reset($entityManager);
        $this->entityManager = $entityManager;
        /** @var ApplicationLifecycleServiceInterface $service */
        $service = $container->get(ApplicationLifecycleServiceInterface::class);
        $this->applicationLifecycleService = $service;
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }

    public function testPublishCannotProceedWithoutManifest(): void
    {
        $application = $this->createApplication('publish-no-manifest');
        $release = $this->createRelease($application, '1.0.0');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Application cannot be published without a manifest.');

        $this->applicationLifecycleService->publishApplication($application, $release);
    }

    public function testPublishCannotProceedWithoutApprovedManifest(): void
    {
        $application = $this->createApplication('publish-review-required');
        $release = $this->createRelease($application, '1.0.0');
        $this->createManifest($application, 'io.applicating.publish.review.required', 'review_required');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Application cannot be published without an approved manifest.');

        $this->applicationLifecycleService->publishApplication($application, $release);
    }

    public function testPublishCannotProceedForAlreadyPublishedRelease(): void
    {
        $application = $this->createApplication('publish-already-published');
        $release = $this->createRelease($application, '1.0.0');
        $this->createManifest($application, 'io.applicating.publish.already.published', 'approved');
        $release->publish();
        $this->entityManager->flush();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Application release is already published.');

        $this->applicationLifecycleService->publishApplication($application, $release);
    }

    private function createApplication(string $slug): Application
    {
        $applicationData = new ApplicationUpsertData();
        $applicationData->name = ucwords(str_replace('-', ' ', $slug));
        $applicationData->slug = $slug;
        $applicationData->packageName = 'applicating/' . $slug;
        $applicationData->developerName = 'Applicating Labs';
        $applicationData->listingSummary = 'Publish guard lifecycle test listing';
        $applicationData->accessLevel = 'public';
        $applicationData->sandboxProfile = 'default';

        return $this->applicationLifecycleService->createApplication($applicationData);
    }

    private function createRelease(Application $application, string $version): ApplicationRelease
    {
        $releaseData = new ApplicationReleaseData();
        $releaseData->version = $version;
        $releaseData->channel = 'stable';
        $releaseData->checksum = hash('sha256', $application->getSlug() . '-' . $version);
        $releaseData->downloadUrl = 'https://downloads.example.test/' . $application->getSlug() . '/' . $version . '.zip';
        $releaseData->releaseNotes = 'Publish guard lifecycle release notes';

        return $this->applicationLifecycleService->createRelease($application, $releaseData);
    }

    private function createManifest(Application $application, string $identifier, string $governanceState): ApplicationManifest
    {
        $manifestData = new ApplicationManifestData();
        $manifestData->manifestVersion = '1.0.0';
        $manifestData->identifier = $identifier;
        $manifestData->capabilities = "catalog\nreporting";
        $manifestData->permissions = 'tenant:read';
        $manifestData->runtimeHooks = 'bootstrap';
        $manifestData->sandboxProfile = 'default';
        $manifestData->governanceState = $governanceState;

        return $this->applicationLifecycleService->createManifest($application, $manifestData);
    }
}
