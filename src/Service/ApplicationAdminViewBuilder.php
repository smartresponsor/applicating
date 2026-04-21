<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\DTO\Application\ApplicationAdminIndexRow;
use App\Application\DTO\Application\ApplicationAdminManifestView;
use App\Application\DTO\Application\ApplicationAdminReleaseView;
use App\Application\DTO\Application\ApplicationAdminShowView;
use App\Application\DTO\Application\ApplicationAdminTenantAssignmentView;
use App\Application\Entity\Application;
use App\Application\Entity\ApplicationManifest;
use App\Application\Entity\ApplicationRelease;
use App\Application\Entity\TenantApplication;
use App\Application\ServiceInterface\ApplicationAdminViewBuilderInterface;

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
