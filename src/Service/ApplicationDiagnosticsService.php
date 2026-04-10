<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Application\TenantApplicationDiagnostics;
use App\DTO\Application\TenantApplicationDiagnosticsChecks;
use App\Entity\TenantApplication;
use App\ServiceInterface\ApplicationDiagnosticsServiceInterface;

final class ApplicationDiagnosticsService implements ApplicationDiagnosticsServiceInterface
{
    public function buildTenantDiagnostics(TenantApplication $tenantApplication): TenantApplicationDiagnostics
    {
        return new TenantApplicationDiagnostics(
            tenantKey: $tenantApplication->getTenantKey(),
            application: $tenantApplication->getApplication()->getSlug(),
            version: $tenantApplication->getInstalledVersion(),
            enabled: $tenantApplication->isEnabled(),
            billingActive: $tenantApplication->isBillingActive(),
            installationState: $tenantApplication->getInstallationState()->value,
            checks: new TenantApplicationDiagnosticsChecks(
                manifestPresent: $tenantApplication->getApplication()->getManifests()->count() > 0,
                releasePresent: $tenantApplication->getApplication()->getReleases()->count() > 0,
                sandboxProfile: $tenantApplication->getApplication()->getSandboxProfile(),
                accessPolicyKeys: array_values(array_map('strval', array_keys($tenantApplication->getAccessPolicy()))),
            ),
            reportedAt: (new \DateTimeImmutable())->format(DATE_ATOM),
        );
    }
}
