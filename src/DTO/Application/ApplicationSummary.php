<?php

declare(strict_types=1);

namespace App\DTO\Application;

final class ApplicationSummary
{
    public function __construct(
        public readonly int $applicationsTotal,
        public readonly int $applicationsPublished,
        public readonly int $tenantAssignmentsTotal,
        public readonly int $tenantAssignmentsEnabled,
        public readonly int $billingActiveTotal,
    ) {
    }

    /** @return array{applicationsTotal:int,applicationsPublished:int,tenantAssignmentsTotal:int,tenantAssignmentsEnabled:int,billingActiveTotal:int} */
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
