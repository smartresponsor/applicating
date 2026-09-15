<?php

declare(strict_types=1);

namespace App\Applicating\Service;

use App\Applicating\DTO\ApplicationSummaryDTO;
use App\Applicating\Repository\ApplicationRepository;
use App\Applicating\Repository\TenantApplicationRepository;
use App\Applicating\ServiceInterface\ApplicationReportServiceInterface;

final readonly class ApplicationReportService implements ApplicationReportServiceInterface
{
    public function __construct(
        private ApplicationRepository $applicationRepository,
        private TenantApplicationRepository $tenantApplicationRepository,
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
