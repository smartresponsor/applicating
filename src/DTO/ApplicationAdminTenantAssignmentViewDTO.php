<?php

declare(strict_types=1);

namespace App\Applicating\DTO;

use App\Applicating\Entity\TenantApplication;

final readonly class ApplicationAdminTenantAssignmentViewDTO
{
    /** @param array<string, mixed> $diagnostics */
    public function __construct(
        public int $id,
        public string $tenantKey,
        public string $installedVersion,
        public string $installationState,
        public bool $billingActive,
        public bool $enabled,
        public array $diagnostics,
    ) {
    }

    public static function fromTenantApplication(TenantApplication $tenantApplication): self
    {
        return new self(
            id: $tenantApplication->getId() ?? 0,
            tenantKey: $tenantApplication->getTenantKey(),
            installedVersion: $tenantApplication->getInstalledVersion(),
            installationState: $tenantApplication->getInstallationState()->value,
            billingActive: $tenantApplication->isBillingActive(),
            enabled: $tenantApplication->isEnabled(),
            diagnostics: $tenantApplication->getDiagnostics(),
        );
    }
}
