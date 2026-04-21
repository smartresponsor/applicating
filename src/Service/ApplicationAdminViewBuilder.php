<?php

declare(strict_types=1);

namespace App\Applicating\Service;

use App\Applicating\DTO\Application\ApplicationAdminIndexRow;
use App\Applicating\DTO\Application\ApplicationAdminManifestView;
use App\Applicating\DTO\Application\ApplicationAdminReleaseView;
use App\Applicating\DTO\Application\ApplicationAdminShowView;
use App\Applicating\DTO\Application\ApplicationAdminTenantAssignmentView;
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
