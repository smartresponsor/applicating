<?php

declare(strict_types=1);

namespace App\Applicating\Service;

use App\Applicating\DTO\ApplicationAdminIndexRowDTO;
use App\Applicating\DTO\ApplicationAdminManifestViewDTO;
use App\Applicating\DTO\ApplicationAdminReleaseViewDTO;
use App\Applicating\DTO\ApplicationAdminShowViewDTO;
use App\Applicating\DTO\ApplicationAdminTenantAssignmentViewDTO;
use App\Applicating\Entity\Application;
use App\Applicating\Entity\ApplicationManifest;
use App\Applicating\Entity\ApplicationRelease;
use App\Applicating\Entity\TenantApplication;
use App\Applicating\ServiceInterface\ApplicationAdminViewBuilderInterface;

final class ApplicationAdminViewBuilder implements ApplicationAdminViewBuilderInterface
{
    public function buildIndexRows(array $applications): array
    {
        return array_map(
            static fn (Application $application): ApplicationAdminIndexRowDTO => ApplicationAdminIndexRowDTO::fromApplication($application),
            $applications,
        );
    }

    public function buildShowView(Application $application): ApplicationAdminShowViewDTO
    {
        $releases = array_values(array_map(
            static fn (ApplicationRelease $release): ApplicationAdminReleaseViewDTO => ApplicationAdminReleaseViewDTO::fromRelease($release),
            $application->getReleases()->toArray(),
        ));
        $manifests = array_values(array_map(
            static fn (ApplicationManifest $manifest): ApplicationAdminManifestViewDTO => ApplicationAdminManifestViewDTO::fromManifest($manifest),
            $application->getManifests()->toArray(),
        ));
        $tenantAssignments = array_values(array_map(
            static fn (TenantApplication $tenantApplication): ApplicationAdminTenantAssignmentViewDTO => ApplicationAdminTenantAssignmentViewDTO::fromTenantApplication($tenantApplication),
            $application->getTenantApplications()->toArray(),
        ));

        return ApplicationAdminShowViewDTO::fromApplication($application, $releases, $manifests, $tenantAssignments);
    }
}
