<?php

declare(strict_types=1);

namespace App\Applicating\Service;

use App\Applicating\DTO\ApplicationSummaryDTO;
use App\Applicating\RepositoryInterface\ApplicationRepositoryInterface;
use App\Applicating\RepositoryInterface\ApplicationTenantAssignmentRepositoryInterface;
use App\Applicating\ServiceInterface\ApplicationReportServiceInterface;

final readonly class ApplicationReportService implements ApplicationReportServiceInterface
{
    public function __construct(
        private ApplicationRepositoryInterface $applicationRepository,
        private ApplicationTenantAssignmentRepositoryInterface $tenantApplicationRepository,
    ) {
    }

    public function buildSummary(): ApplicationSummaryDTO
    {
        return new ApplicationSummaryDTO(
            applicationsTotal: $this->applicationRepository->countAllApplications(),
            applicationsPublished: $this->applicationRepository->countPublished(),
            tenantAssignmentsTotal: $this->tenantApplicationRepository->countAllAssignments(),
            tenantAssignmentsEnabled: $this->tenantApplicationRepository->countEnabledAssignments(),
            billingActiveTotal: $this->tenantApplicationRepository->countBillingActiveAssignments(),
        );
    }
}
