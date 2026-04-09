<?php

declare(strict_types=1);

namespace App\DTO\Application;

final class TenantApplicationDiagnostics
{
    public function __construct(
        public readonly string $tenantKey,
        public readonly string $application,
        public readonly string $version,
        public readonly bool $enabled,
        public readonly bool $billingActive,
        public readonly string $installationState,
        public readonly TenantApplicationDiagnosticsChecks $checks,
        public readonly string $reportedAt,
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
