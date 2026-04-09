<?php

declare(strict_types=1);

namespace App\DTO\Application;

use App\Entity\TenantApplication;

final class ApplicationAdminTenantAssignmentView
{
    /** @param array<string, mixed> $diagnostics */
    public function __construct(
        public readonly int $id,
        public readonly string $tenantKey,
        public readonly string $installedVersion,
        public readonly string $installationState,
        public readonly bool $billingActive,
        public readonly bool $enabled,
        public readonly array $diagnostics,
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
