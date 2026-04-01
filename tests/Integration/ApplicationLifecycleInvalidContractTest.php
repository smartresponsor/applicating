<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\DTO\Application\ApplicationReleaseData;
use App\DTO\Application\ApplicationUpsertData;
use App\DTO\Application\TenantApplicationAssignmentData;
use App\Entity\Application;
use App\Repository\ApplicationRepository;
use App\Repository\TenantApplicationRepository;
use App\ServiceInterface\ApplicationLifecycleServiceInterface;
use App\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ApplicationLifecycleInvalidContractTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private ApplicationLifecycleServiceInterface $applicationLifecycleService;
    private ApplicationRepository $applicationRepository;
    private TenantApplicationRepository $tenantApplicationRepository;

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

        /** @var ApplicationLifecycleServiceInterface $applicationLifecycleService */
        $applicationLifecycleService = $container->get(ApplicationLifecycleServiceInterface::class);
        $this->applicationLifecycleService = $applicationLifecycleService;

        /** @var ApplicationRepository $applicationRepository */
        $applicationRepository = $container->get(ApplicationRepository::class);
        $this->applicationRepository = $applicationRepository;

        /** @var TenantApplicationRepository $tenantApplicationRepository */
        $tenantApplicationRepository = $container->get(TenantApplicationRepository::class);
        $this->tenantApplicationRepository = $tenantApplicationRepository;
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }

    public function testCreateApplicationRejectsInvalidSlugAndPersistsNothing(): void
    {
        $data = new ApplicationUpsertData();
        $data->name = 'Invalid Slug Application';
        $data->slug = 'Invalid Slug';
        $data->packageName = 'applicating/invalid-slug-application';
        $data->developerName = 'Applicating Labs';
        $data->listingSummary = 'Invalid slug summary';
        $data->accessLevel = 'public';
        $data->sandboxProfile = 'default';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Application slug must use lowercase kebab-case.');

        try {
            $this->applicationLifecycleService->createApplication($data);
        } finally {
            $this->entityManager->clear();
            self::assertCount(0, $this->applicationRepository->findOrderedForAdmin());
        }
    }

    public function testCreateReleaseRejectsInvalidVersionAndPersistsNothing(): void
    {
        $application = $this->createValidApplication('invalid-release-version');

        $releaseData = new ApplicationReleaseData();
        $releaseData->version = '1.0';
        $releaseData->channel = 'stable';
        $releaseData->checksum = hash('sha256', 'invalid-release-version');
        $releaseData->downloadUrl = 'https://downloads.example.test/invalid-release-version/1.0.zip';
        $releaseData->releaseNotes = 'Invalid release version';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Application version must use semver-like format.');

        try {
            $this->applicationLifecycleService->createRelease($application, $releaseData);
        } finally {
            $this->entityManager->clear();
            $persisted = $this->applicationRepository->findOneBy(['slug' => 'invalid-release-version']);
            self::assertInstanceOf(Application::class, $persisted);
            self::assertCount(0, $persisted->getReleases());
        }
    }

    public function testAssignTenantRejectsInvalidJsonPolicyAndPersistsNothing(): void
    {
        $application = $this->createValidApplication('invalid-assignment-policy');

        $assignmentData = new TenantApplicationAssignmentData();
        $assignmentData->tenantKey = 'tenant-invalid-policy';
        $assignmentData->installedVersion = '1.0.0';
        $assignmentData->enabled = true;
        $assignmentData->billingActive = true;
        $assignmentData->accessPolicy = '{bad-json}';

        $this->expectException(\JsonException::class);

        try {
            $this->applicationLifecycleService->assignTenant($application, $assignmentData);
        } finally {
            $this->entityManager->clear();
            self::assertCount(0, $this->tenantApplicationRepository->findAll());
        }
    }

    private function createValidApplication(string $slug): Application
    {
        $data = new ApplicationUpsertData();
        $data->name = ucfirst(str_replace('-', ' ', $slug));
        $data->slug = $slug;
        $data->packageName = 'applicating/' . $slug;
        $data->developerName = 'Applicating Labs';
        $data->listingSummary = 'Valid lifecycle base';
        $data->accessLevel = 'public';
        $data->sandboxProfile = 'default';

        return $this->applicationLifecycleService->createApplication($data);
    }
}
