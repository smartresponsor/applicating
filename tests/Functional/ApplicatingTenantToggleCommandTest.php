<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\DTO\Application\ApplicationReleaseData;
use App\DTO\Application\ApplicationUpsertData;
use App\DTO\Application\TenantApplicationAssignmentData;
use App\Repository\TenantApplicationRepository;
use App\ServiceInterface\ApplicationLifecycleServiceInterface;
use App\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

final class ApplicatingTenantToggleCommandTest extends KernelTestCase
{
    public function testCommandDisablesAndEnablesTenantApplication(): void
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
        $applicationData = new ApplicationUpsertData();
        $applicationData->name = 'CLI Managed Application';
        $applicationData->slug = 'cli-managed-application';
        $applicationData->packageName = 'applicating/cli-managed-application';
        $applicationData->developerName = 'Applicating Labs';
        $applicationData->listingSummary = 'CLI management target';
        $application = $service->createApplication($applicationData);

        $releaseData = new ApplicationReleaseData();
        $releaseData->version = '1.0.0';
        $releaseData->checksum = hash('sha256', 'cli-managed-application');
        $releaseData->downloadUrl = 'https://downloads.example.test/cli-managed-application/1.0.0.zip';
        $releaseData->releaseNotes = 'CLI release';
        $service->createRelease($application, $releaseData);

        $assignmentData = new TenantApplicationAssignmentData();
        $assignmentData->tenantKey = 'tenant-cli';
        $assignmentData->installedVersion = '1.0.0';
        $assignmentData->enabled = true;
        $assignmentData->billingActive = true;
        $assignmentData->accessPolicy = json_encode(['scope' => 'tenant'], JSON_THROW_ON_ERROR);
        $service->assignTenant($application, $assignmentData);

        /** @var KernelInterface $kernel */
        $kernel = self::$kernel;
        $console = new Application($kernel);
        $command = $console->find('applicating:tenant:toggle');

        $disableTester = new CommandTester($command);
        $disableTester->execute([
            'tenantKey' => 'tenant-cli',
            'applicationSlug' => 'cli-managed-application',
            '--disable' => true,
        ]);

        self::assertSame(0, $disableTester->getStatusCode());
        self::assertStringContainsString('disabled', $disableTester->getDisplay());

        /** @var TenantApplicationRepository $repository */
        $repository = $container->get(TenantApplicationRepository::class);
        $disabledAssignment = $repository->findOneForTenantAndApplication('tenant-cli', 'cli-managed-application');
        self::assertNotNull($disabledAssignment);
        self::assertFalse($disabledAssignment->isEnabled());

        $enableTester = new CommandTester($command);
        $enableTester->execute([
            'tenantKey' => 'tenant-cli',
            'applicationSlug' => 'cli-managed-application',
        ]);

        self::assertSame(0, $enableTester->getStatusCode());
        self::assertStringContainsString('enabled', $enableTester->getDisplay());

        $enabledAssignment = $repository->findOneForTenantAndApplication('tenant-cli', 'cli-managed-application');
        self::assertNotNull($enabledAssignment);
        self::assertTrue($enabledAssignment->isEnabled());
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }
}
