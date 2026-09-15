<?php

declare(strict_types=1);

namespace App\Applicating\Service;

use App\Applicating\DTO\TenantApplicationDiagnosticsChecksDTO;
use App\Applicating\DTO\TenantApplicationDiagnosticsDTO;
use App\Applicating\Entity\TenantApplication;
use App\Applicating\ServiceInterface\ApplicationDiagnosticsServiceInterface;

final class ApplicationDiagnosticsService implements ApplicationDiagnosticsServiceInterface
{
    public function buildTenantDiagnostics(TenantApplication $tenantApplication): TenantApplicationDiagnosticsDTO
    {
        return new TenantApplicationDiagnosticsDTO(
            tenantKey: $tenantApplication->getTenantKey(),
            application: $tenantApplication->getApplication()->getSlug(),
            version: $tenantApplication->getInstalledVersion(),
            enabled: $tenantApplication->isEnabled(),
            billingActive: $tenantApplication->isBillingActive(),
            installationState: $tenantApplication->getInstallationState()->value,
            checks: new TenantApplicationDiagnosticsChecksDTO(
                manifestPresent: $tenantApplication->getApplication()->getManifests()->count() > 0,
                releasePresent: $tenantApplication->getApplication()->getReleases()->count() > 0,
                sandboxProfile: $tenantApplication->getApplication()->getSandboxProfile(),
                accessPolicyKeys: array_map('strval', array_keys($tenantApplication->getAccessPolicy())),
            ),
            reportedAt: (new \DateTimeImmutable())->format(DATE_ATOM),
        );
    }
}
