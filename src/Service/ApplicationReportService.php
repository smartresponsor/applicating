<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ApplicationRepository;
use App\Repository\TenantApplicationRepository;
use App\ServiceInterface\ApplicationReportServiceInterface;

final class ApplicationReportService implements ApplicationReportServiceInterface
{
    public function __construct(
        private readonly ApplicationRepository $applicationRepository,
        private readonly TenantApplicationRepository $tenantApplicationRepository,
    ) {
    }

    public function buildSummary(): array
    {
        $applications = $this->applicationRepository->findOrderedForAdmin();
        $tenantAssignments = $this->tenantApplicationRepository->findAll();

        return [
            'applicationsTotal' => count($applications),
            'applicationsPublished' => $this->applicationRepository->countPublished(),
            'tenantAssignmentsTotal' => count($tenantAssignments),
            'tenantAssignmentsEnabled' => count(array_filter($tenantAssignments, static fn ($assignment): bool => $assignment->isEnabled())),
            'billingActiveTotal' => count(array_filter($tenantAssignments, static fn ($assignment): bool => $assignment->isBillingActive())),
        ];
    }
}
