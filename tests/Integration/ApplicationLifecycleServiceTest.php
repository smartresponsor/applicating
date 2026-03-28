<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\DTO\Application\ApplicationManifestData;
use App\DTO\Application\ApplicationReleaseData;
use App\DTO\Application\ApplicationUpsertData;
use App\DTO\Application\TenantApplicationAssignmentData;
use App\Entity\Application;
use App\ServiceInterface\ApplicationLifecycleServiceInterface;
use App\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ApplicationLifecycleServiceTest extends KernelTestCase
{
    private ApplicationLifecycleServiceInterface $applicationLifecycleService;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        /** @var ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();
        DoctrineSchemaResetter::reset($entityManager);
        /** @var ApplicationLifecycleServiceInterface $service */
        $service = $container->get(ApplicationLifecycleServiceInterface::class);
        $this->applicationLifecycleService = $service;
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }

    public function testLifecycleFlow(): void
    {
        $applicationData = new ApplicationUpsertData();
        $applicationData->name = 'Demo Application';
        $applicationData->slug = 'demo-application';
        $applicationData->packageName = 'applicating/demo-application';
        $applicationData->developerName = 'Applicating Labs';
        $applicationData->listingSummary = 'Demo listing';

        $application = $this->applicationLifecycleService->createApplication($applicationData);
        self::assertInstanceOf(Application::class, $application);

        $releaseData = new ApplicationReleaseData();
        $releaseData->version = '1.0.0';
        $releaseData->checksum = hash('sha256', 'demo');
        $releaseData->downloadUrl = 'https://downloads.example.test/demo-application/1.0.0.zip';
        $releaseData->releaseNotes = 'First release';
        $release = $this->applicationLifecycleService->createRelease($application, $releaseData);

        $manifestData = new ApplicationManifestData();
        $manifestData->identifier = 'io.applicating.demo.application';
        $manifestData->capabilities = "catalog\nreporting";
        $manifestData->permissions = 'tenant:read';
        $manifestData->runtimeHooks = 'bootstrap';
        $manifest = $this->applicationLifecycleService->createManifest($application, $manifestData);

        $this->applicationLifecycleService->publishApplication($application, $release);

        $assignmentData = new TenantApplicationAssignmentData();
        $assignmentData->tenantKey = 'tenant-alpha';
        $assignmentData->installedVersion = '1.0.0';
        $assignmentData->accessPolicy = json_encode(['scope' => 'tenant'], JSON_THROW_ON_ERROR);
        $assignment = $this->applicationLifecycleService->assignTenant($application, $assignmentData);

        self::assertSame('published', $application->getPublicationState()->value);
        self::assertSame('published', $release->getPublicationState()->value);
        self::assertSame('io.applicating.demo.application', $manifest->getIdentifier());
        self::assertSame('tenant-alpha', $assignment->getTenantKey());
        self::assertTrue($assignment->isEnabled());

        $this->applicationLifecycleService->suspendApplication($application);
        self::assertSame('suspended', $application->getPublicationState()->value);
    }
}
