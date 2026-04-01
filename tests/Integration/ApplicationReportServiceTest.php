<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\Application;
use App\Entity\TenantApplication;
use App\ServiceInterface\ApplicationReportServiceInterface;
use App\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ApplicationReportServiceTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private ApplicationReportServiceInterface $applicationReportService;

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

        /** @var ApplicationReportServiceInterface $service */
        $service = $container->get(ApplicationReportServiceInterface::class);
        $this->applicationReportService = $service;
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }

    public function testBuildSummaryReturnsPublishedAndAssignmentTotals(): void
    {
        $application = new Application(
            'Report Demo',
            'report-demo',
            'applicating/report-demo',
            'Applicating Labs',
            'Report listing summary'
        );
        $application->publish();

        $tenantApplication = new TenantApplication(
            $application,
            'tenant-report',
            '1.0.0',
            true,
            true,
            ['scope' => 'tenant']
        );
        $application->addTenantApplication($tenantApplication);

        $this->entityManager->persist($application);
        $this->entityManager->persist($tenantApplication);
        $this->entityManager->flush();

        $summary = $this->applicationReportService->buildSummary();

        self::assertSame(1, $summary['applicationsTotal']);
        self::assertSame(1, $summary['applicationsPublished']);
        self::assertSame(1, $summary['tenantAssignmentsTotal']);
        self::assertSame(1, $summary['tenantAssignmentsEnabled']);
        self::assertSame(1, $summary['billingActiveTotal']);
    }
}
