<?php

declare(strict_types=1);

namespace App\Applicating\Service;

use App\Applicating\DTO\ApplicationTenantDiagnosticsChecksDTO;
use App\Applicating\DTO\ApplicationTenantDiagnosticsDTO;
use App\Applicating\Entity\ApplicationTenantAssignmentEntity;
use App\Applicating\ServiceInterface\ApplicationDiagnosticsServiceInterface;

final class ApplicationDiagnosticsService implements ApplicationDiagnosticsServiceInterface
{
    public function buildTenantDiagnostics(ApplicationTenantAssignmentEntity $tenantApplication): ApplicationTenantDiagnosticsDTO
    {
        return new ApplicationTenantDiagnosticsDTO(
            tenantKey: $tenantApplication->getTenantKey(),
            application: $tenantApplication->getApplication()->getSlug(),
            version: $tenantApplication->getInstalledVersion(),
            enabled: $tenantApplication->isEnabled(),
            billingActive: $tenantApplication->isBillingActive(),
            installationState: $tenantApplication->getInstallationState()->value,
            checks: new ApplicationTenantDiagnosticsChecksDTO(
                manifestPresent: $tenantApplication->getApplication()->getManifests()->count() > 0,
                releasePresent: $tenantApplication->getApplication()->getReleases()->count() > 0,
                sandboxProfile: $tenantApplication->getApplication()->getSandboxProfile(),
                accessPolicyKeys: array_map('strval', array_keys($tenantApplication->getAccessPolicy())),
            ),
            reportedAt: (new \DateTimeImmutable())->format(DATE_ATOM),
        );
    }
}
