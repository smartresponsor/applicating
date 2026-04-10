<?php

declare(strict_types=1);

namespace App\DTO\Application;

final readonly class TenantApplicationDiagnostics
{
    public function __construct(
        public string $tenantKey,
        public string $application,
        public string $version,
        public bool $enabled,
        public bool $billingActive,
        public string $installationState,
        public TenantApplicationDiagnosticsChecks $checks,
        public string $reportedAt,
    ) {
    }

    /** @return array{tenantKey:string,application:string,version:string,enabled:bool,billingActive:bool,installationState:string,checks:array{manifest_present:bool,release_present:bool,sandbox_profile:string,access_policy_keys:list<string>},reportedAt:string} */
    public function toArray(): array
    {
        return [
            'tenantKey' => $this->tenantKey,
            'application' => $this->application,
            'version' => $this->version,
            'enabled' => $this->enabled,
            'billingActive' => $this->billingActive,
            'installationState' => $this->installationState,
            'checks' => $this->checks->toArray(),
            'reportedAt' => $this->reportedAt,
        ];
    }
}
