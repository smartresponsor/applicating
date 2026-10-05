<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\Service;

use App\Applicating\RepositoryInterface\ApplicationRepositoryInterface;
use App\Applicating\RepositoryInterface\ApplicationTenantAssignmentRepositoryInterface;
use App\Applicating\Service\ApplicationReportService;
use PHPUnit\Framework\TestCase;

final class ApplicationReportServiceTest extends TestCase
{
    public function testBuildSummaryAggregatesRepositoryCounts(): void
    {
        $applicationRepository = $this->createMock(ApplicationRepositoryInterface::class);
        $tenantRepository = $this->createMock(ApplicationTenantAssignmentRepositoryInterface::class);

        $applicationRepository->expects(self::once())->method('countAllApplications')->willReturn(12);
        $applicationRepository->expects(self::once())->method('countPublished')->willReturn(7);
        $tenantRepository->expects(self::once())->method('countAllAssignments')->willReturn(25);
        $tenantRepository->expects(self::once())->method('countEnabledAssignments')->willReturn(19);
        $tenantRepository->expects(self::once())->method('countBillingActiveAssignments')->willReturn(14);

        $summary = (new ApplicationReportService($applicationRepository, $tenantRepository))->buildSummary();

        self::assertSame([
            'applicationsTotal' => 12,
            'applicationsPublished' => 7,
            'tenantAssignmentsTotal' => 25,
            'tenantAssignmentsEnabled' => 19,
            'billingActiveTotal' => 14,
        ], $summary->toArray());
    }
}
