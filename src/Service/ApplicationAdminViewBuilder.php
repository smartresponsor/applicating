<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Application\ApplicationAdminIndexRow;
use App\DTO\Application\ApplicationAdminManifestView;
use App\DTO\Application\ApplicationAdminReleaseView;
use App\DTO\Application\ApplicationAdminShowView;
use App\DTO\Application\ApplicationAdminTenantAssignmentView;
use App\Entity\Application;
use App\Entity\ApplicationManifest;
use App\Entity\ApplicationRelease;
use App\Entity\TenantApplication;
use App\ServiceInterface\ApplicationAdminViewBuilderInterface;

final class ApplicationAdminViewBuilder implements ApplicationAdminViewBuilderInterface
{
    public function buildIndexRows(array $applications): array
    {
        return array_map(
            static fn (Application $application): ApplicationAdminIndexRow => ApplicationAdminIndexRow::fromApplication($application),
            $applications,
        );
    }

    public function buildShowView(Application $application): ApplicationAdminShowView
    {
        $releases = array_map(
            static fn (ApplicationRelease $release): ApplicationAdminReleaseView => ApplicationAdminReleaseView::fromRelease($release),
            $application->getReleases()->toArray(),
        );
        $manifests = array_map(
            static fn (ApplicationManifest $manifest): ApplicationAdminManifestView => ApplicationAdminManifestView::fromManifest($manifest),
            $application->getManifests()->toArray(),
        );
        $tenantAssignments = array_map(
            static fn (TenantApplication $tenantApplication): ApplicationAdminTenantAssignmentView => ApplicationAdminTenantAssignmentView::fromTenantApplication($tenantApplication),
            $application->getTenantApplications()->toArray(),
        );

        return ApplicationAdminShowView::fromApplication($application, $releases, $manifests, $tenantAssignments);
    }
}
