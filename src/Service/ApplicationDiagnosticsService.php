<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\TenantApplication;
use App\ServiceInterface\ApplicationDiagnosticsServiceInterface;

final class ApplicationDiagnosticsService implements ApplicationDiagnosticsServiceInterface
{
    public function buildTenantDiagnostics(TenantApplication $tenantApplication): array
    {
        return [
            'tenantKey' => $tenantApplication->getTenantKey(),
            'application' => $tenantApplication->getApplication()->getSlug(),
            'version' => $tenantApplication->getInstalledVersion(),
            'enabled' => $tenantApplication->isEnabled(),
            'billingActive' => $tenantApplication->isBillingActive(),
            'installationState' => $tenantApplication->getInstallationState()->value,
            'checks' => [
                'manifest_present' => $tenantApplication->getApplication()->getManifests()->count() > 0,
                'release_present' => $tenantApplication->getApplication()->getReleases()->count() > 0,
                'sandbox_profile' => $tenantApplication->getApplication()->getSandboxProfile(),
                'access_policy_keys' => array_keys($tenantApplication->getAccessPolicy()),
            ],
            'reportedAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ];
    }
}
