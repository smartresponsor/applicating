<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\DTO\Application\ApplicationManifestData;
use App\DTO\Application\ApplicationReleaseData;
use App\DTO\Application\ApplicationUpsertData;
use App\DTO\Application\TenantApplicationAssignmentData;
use App\Entity\Application;
use App\Entity\TenantApplication;
use App\Repository\ApplicationRepository;
use App\Repository\TenantApplicationRepository;
use App\ServiceInterface\ApplicationLifecycleServiceInterface;
use App\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ApplicationLifecycleStateManagementTest extends KernelTestCase
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

    public function testAssignTenantParsesPolicyAndStoresDiagnostics(): void
    {
        $application = $this->createApplicationAggregate('state-management-app');

        $assignmentData = new TenantApplicationAssignmentData();
        $assignmentData->tenantKey = 'tenant-state-management';
        $assignmentData->installedVersion = '1.0.0';
        $assignmentData->enabled = true;
        $assignmentData->billingActive = true;
        $assignmentData->accessPolicy = json_encode([
            'scope' => 'tenant',
            'roles' => ['ROLE_APPLICATION_USER'],
        ], JSON_THROW_ON_ERROR);

        $assignment = $this->applicationLifecycleService->assignTenant($application, $assignmentData);

        $this->entityManager->clear();
        $persisted = $this->tenantApplicationRepository->find($assignment->getId());
        self::assertInstanceOf(TenantApplication::class, $persisted);
        self::assertSame('tenant-state-management', $persisted->getTenantKey());
        self::assertSame('1.0.0', $persisted->getInstalledVersion());
        self::assertTrue($persisted->isEnabled());
        self::assertTrue($persisted->isBillingActive());
        self::assertSame(['scope' => 'tenant', 'roles' => ['ROLE_APPLICATION_USER']], $persisted->getAccessPolicy());
        self::assertTrue($persisted->getDiagnostics()['checks']['manifest_present']);
        self::assertTrue($persisted->getDiagnostics()['checks']['release_present']);
        self::assertSame('default', $persisted->getDiagnostics()['checks']['sandbox_profile']);
    }

    public function testToggleTenantApplicationUpdatesStateAndDiagnostics(): void
    {
        $application = $this->createApplicationAggregate('toggle-state-app');

        $assignmentData = new TenantApplicationAssignmentData();
        $assignmentData->tenantKey = 'tenant-toggle-state';
        $assignmentData->installedVersion = '1.0.0';
        $assignmentData->enabled = true;
        $assignmentData->billingActive = true;
        $assignmentData->accessPolicy = json_encode(['scope' => 'tenant'], JSON_THROW_ON_ERROR);
        $assignment = $this->applicationLifecycleService->assignTenant($application, $assignmentData);

        $this->applicationLifecycleService->toggleTenantApplication($assignment, false);
        $this->entityManager->clear();
        $disabled = $this->tenantApplicationRepository->find($assignment->getId());
        self::assertInstanceOf(TenantApplication::class, $disabled);
        self::assertFalse($disabled->isEnabled());
        self::assertSame('disabled', $disabled->getInstallationState()->value);
        self::assertFalse($disabled->getDiagnostics()['enabled']);

        $this->applicationLifecycleService->toggleTenantApplication($disabled, true);
        $this->entityManager->clear();
        $enabled = $this->tenantApplicationRepository->find($assignment->getId());
        self::assertInstanceOf(TenantApplication::class, $enabled);
        self::assertTrue($enabled->isEnabled());
        self::assertSame('installed', $enabled->getInstallationState()->value);
        self::assertTrue($enabled->getDiagnostics()['enabled']);
    }

    public function testSuspendApplicationPersistsSuspendedPublicationState(): void
    {
        $application = $this->createApplicationAggregate('suspend-state-app', true);

        $this->applicationLifecycleService->suspendApplication($application);

        $this->entityManager->clear();
        $persisted = $this->applicationRepository->findOneBy(['slug' => 'suspend-state-app']);
        self::assertInstanceOf(Application::class, $persisted);
        self::assertSame('suspended', $persisted->getPublicationState()->value);
    }

    private function createApplicationAggregate(string $slug, bool $published = false): Application
    {
        $applicationData = new ApplicationUpsertData();
        $applicationData->name = ucfirst(str_replace('-', ' ', $slug));
        $applicationData->slug = $slug;
        $applicationData->packageName = 'applicating/' . $slug;
        $applicationData->developerName = 'Applicating Labs';
        $applicationData->listingSummary = 'Lifecycle state management summary';
        $applicationData->accessLevel = 'public';
        $applicationData->sandboxProfile = 'default';
        $application = $this->applicationLifecycleService->createApplication($applicationData);

        $releaseData = new ApplicationReleaseData();
        $releaseData->version = '1.0.0';
        $releaseData->channel = 'stable';
        $releaseData->checksum = hash('sha256', $slug);
        $releaseData->downloadUrl = 'https://downloads.example.test/' . $slug . '/1.0.0.zip';
        $releaseData->releaseNotes = 'Lifecycle release notes';
        $release = $this->applicationLifecycleService->createRelease($application, $releaseData);

        $manifestData = new ApplicationManifestData();
        $manifestData->identifier = 'io.applicating.' . str_replace('-', '.', $slug);
        $manifestData->capabilities = "catalog\nreporting";
        $manifestData->permissions = 'tenant:read';
        $manifestData->runtimeHooks = 'bootstrap';
        $manifestData->sandboxProfile = 'default';
        $manifestData->governanceState = 'approved';
        $this->applicationLifecycleService->createManifest($application, $manifestData);

        if ($published) {
            $this->applicationLifecycleService->publishApplication($application, $release);
        }

        return $application;
    }
}
