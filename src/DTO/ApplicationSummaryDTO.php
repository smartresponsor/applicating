<?php

declare(strict_types=1);

namespace App\Applicating\DTO;

/**
 * Carries aggregate application and tenant-assignment counts for reporting surfaces.
 */
final readonly class ApplicationSummaryDTO
{
    public function __construct(
        public int $applicationsTotal,
        public int $applicationsPublished,
        public int $tenantAssignmentsTotal,
        public int $tenantAssignmentsEnabled,
        public int $billingActiveTotal,
    ) {
    }

    /**
     * Returns the stable JSON-ready aggregate summary shape.
     *
     * @return array{applicationsTotal:int,applicationsPublished:int,tenantAssignmentsTotal:int,tenantAssignmentsEnabled:int,billingActiveTotal:int}
     */
    public function toArray(): array
    {
        return [
            'applicationsTotal' => $this->applicationsTotal,
            'applicationsPublished' => $this->applicationsPublished,
            'tenantAssignmentsTotal' => $this->tenantAssignmentsTotal,
            'tenantAssignmentsEnabled' => $this->tenantAssignmentsEnabled,
            'billingActiveTotal' => $this->billingActiveTotal,
        ];
    }
}
