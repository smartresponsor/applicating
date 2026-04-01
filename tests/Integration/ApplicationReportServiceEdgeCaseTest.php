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

final class ApplicationReportServiceEdgeCaseTest extends KernelTestCase
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

    public function testBuildSummaryReturnsZerosForEmptyLandscape(): void
    {
        $summary = $this->applicationReportService->buildSummary();

        self::assertSame(0, $summary['applicationsTotal']);
        self::assertSame(0, $summary['applicationsPublished']);
        self::assertSame(0, $summary['tenantAssignmentsTotal']);
        self::assertSame(0, $summary['tenantAssignmentsEnabled']);
        self::assertSame(0, $summary['billingActiveTotal']);
    }

    public function testBuildSummaryCountsMixedTenantAssignmentStates(): void
    {
        $publishedApplication = new Application(
            'Published Report Application',
            'published-report-application',
            'applicating/published-report-application',
            'Applicating Labs',
            'Published report summary'
        );
        $publishedApplication->publish();

        $draftApplication = new Application(
            'Draft Report Application',
            'draft-report-application',
            'applicating/draft-report-application',
            'Applicating Labs',
            'Draft report summary'
        );

        $enabledBillingAssignment = new TenantApplication(
            $publishedApplication,
            'tenant-report-one',
            '1.0.0',
            true,
            true,
            ['scope' => 'tenant']
        );
        $disabledNoBillingAssignment = new TenantApplication(
            $draftApplication,
            'tenant-report-two',
            '1.0.0',
            false,
            false,
            ['scope' => 'tenant']
        );
        $enabledNoBillingAssignment = new TenantApplication(
            $draftApplication,
            'tenant-report-three',
            '1.0.0',
            true,
            false,
            ['scope' => 'tenant']
        );

        $publishedApplication->addTenantApplication($enabledBillingAssignment);
        $draftApplication->addTenantApplication($disabledNoBillingAssignment);
        $draftApplication->addTenantApplication($enabledNoBillingAssignment);

        $this->entityManager->persist($publishedApplication);
        $this->entityManager->persist($draftApplication);
        $this->entityManager->persist($enabledBillingAssignment);
        $this->entityManager->persist($disabledNoBillingAssignment);
        $this->entityManager->persist($enabledNoBillingAssignment);
        $this->entityManager->flush();

        $summary = $this->applicationReportService->buildSummary();

        self::assertSame(2, $summary['applicationsTotal']);
        self::assertSame(1, $summary['applicationsPublished']);
        self::assertSame(3, $summary['tenantAssignmentsTotal']);
        self::assertSame(2, $summary['tenantAssignmentsEnabled']);
        self::assertSame(1, $summary['billingActiveTotal']);
    }
}
