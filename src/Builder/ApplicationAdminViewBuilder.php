<?php

declare(strict_types=1);

namespace App\Applicating\Builder;

use App\Applicating\BuilderInterface\ApplicationAdminViewBuilderInterface;
use App\Applicating\DTO\ApplicationAdminIndexRowDTO;
use App\Applicating\DTO\ApplicationAdminManifestViewDTO;
use App\Applicating\DTO\ApplicationAdminReleaseViewDTO;
use App\Applicating\DTO\ApplicationAdminShowViewDTO;
use App\Applicating\DTO\ApplicationAdminTenantAssignmentViewDTO;
use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Entity\ApplicationManifestEntity;
use App\Applicating\Entity\ApplicationReleaseEntity;
use App\Applicating\Entity\ApplicationTenantAssignmentEntity;

final class ApplicationAdminViewBuilder implements ApplicationAdminViewBuilderInterface
{
    public function buildIndexRows(array $applications): array
    {
        return array_map(
            static fn (ApplicationEntity $application): ApplicationAdminIndexRowDTO => ApplicationAdminIndexRowDTO::fromApplication($application),
            $applications,
        );
    }

    public function buildShowView(ApplicationEntity $application): ApplicationAdminShowViewDTO
    {
        $releases = array_values(array_map(
            static fn (ApplicationReleaseEntity $release): ApplicationAdminReleaseViewDTO => ApplicationAdminReleaseViewDTO::fromRelease($release),
            $application->getReleases()->toArray(),
        ));
        $manifests = array_values(array_map(
            static fn (ApplicationManifestEntity $manifest): ApplicationAdminManifestViewDTO => ApplicationAdminManifestViewDTO::fromManifest($manifest),
            $application->getManifests()->toArray(),
        ));
        $tenantAssignments = array_values(array_map(
            static fn (ApplicationTenantAssignmentEntity $tenantApplication): ApplicationAdminTenantAssignmentViewDTO => ApplicationAdminTenantAssignmentViewDTO::fromTenantApplication($tenantApplication),
            $application->getTenantApplications()->toArray(),
        ));

        return ApplicationAdminShowViewDTO::fromApplication($application, $releases, $manifests, $tenantAssignments);
    }
}
