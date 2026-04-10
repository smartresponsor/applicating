<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Application\ApplicationSummary;
use App\Repository\ApplicationRepository;
use App\Repository\TenantApplicationRepository;
use App\ServiceInterface\ApplicationReportServiceInterface;

final readonly class ApplicationReportService implements ApplicationReportServiceInterface
{
    public function __construct(
        private ApplicationRepository $applicationRepository,
        private TenantApplicationRepository $tenantApplicationRepository,
    ) {
    }

    public function buildSummary(): ApplicationSummary
    {
        return new ApplicationSummary(
            applicationsTotal: $this->applicationRepository->countAllApplications(),
            applicationsPublished: $this->applicationRepository->countPublished(),
            tenantAssignmentsTotal: $this->tenantApplicationRepository->countAllAssignments(),
            tenantAssignmentsEnabled: $this->tenantApplicationRepository->countEnabledAssignments(),
            billingActiveTotal: $this->tenantApplicationRepository->countBillingActiveAssignments(),
        );
    }
}
