<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\DTO;

use App\Applicating\DTO\ApplicationAdminApiRowDTO;
use App\Applicating\DTO\ApplicationAdminIndexRowDTO;
use App\Applicating\DTO\ApplicationAdminManifestViewDTO;
use App\Applicating\DTO\ApplicationAdminReleaseViewDTO;
use App\Applicating\DTO\ApplicationAdminShowViewDTO;
use App\Applicating\DTO\ApplicationAdminTenantAssignmentViewDTO;
use App\Applicating\DTO\ApplicationSummaryDTO;
use App\Applicating\DTO\ApplicationTenantDiagnosticsChecksDTO;
use App\Applicating\DTO\ApplicationTenantDiagnosticsDTO;
use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Entity\ApplicationManifestEntity;
use App\Applicating\Entity\ApplicationReleaseEntity;
use App\Applicating\Entity\ApplicationTenantAssignmentEntity;
use PHPUnit\Framework\TestCase;

final class ApplicationAdminDTOTest extends TestCase
{
    public function testAdminViewFactoriesMapApplicationState(): void
    {
        $application = new ApplicationEntity(
            'Demo Application',
            'demo-application',
            'demo/application',
            'Demo Developer',
            'Demo summary.',
        );
        $application->publish();

        $release = new ApplicationReleaseEntity(
            $application,
            '1.2.3',
            'stable',
            'abc123',
            'https://example.test/app.zip',
            'Release notes.',
        );
        $manifest = new ApplicationManifestEntity(
            $application,
            '1.0',
            'demo.manifest',
            ['search'],
            [],
            ['boot'],
            'isolated',
            'approved',
            [],
        );
        $assignment = new ApplicationTenantAssignmentEntity(
            $application,
            'tenant-1',
            '1.2.3',
            true,
            true,
            ['healthy' => true],
        );
        $assignment->setDiagnostics(['healthy' => true]);

        $application->addRelease($release);
        $application->addManifest($manifest);
        $application->addTenantApplication($assignment);

        $apiRow = ApplicationAdminApiRowDTO::fromApplication($application);
        self::assertSame([
            'id' => 0,
            'nameEntity' => $application->getName(),
            'slug' => $application->getSlug(),
            'packageName' => $application->getPackageName(),
            'developerName' => $application->getDeveloperName(),
            'publicationState' => $application->getPublicationState()->value,
            'accessLevel' => $application->getAccessLevel()->value,
            'releaseCount' => 1,
            'tenantAssignmentCount' => 1,
        ], $apiRow->toArray());

        $indexRow = ApplicationAdminIndexRowDTO::fromApplication($application);
        self::assertSame(0, $indexRow->id);
        self::assertSame($application->getName(), $indexRow->nameEntity);
        self::assertSame($application->getPackageName(), $indexRow->packageName);
        self::assertSame($application->getSlug(), $indexRow->slug);
        self::assertSame($application->getPublicationState()->value, $indexRow->publicationState);
        self::assertSame($application->getAccessLevel()->value, $indexRow->accessLevel);
        self::assertSame(1, $indexRow->releaseCount);
        self::assertSame(1, $indexRow->tenantAssignmentCount);

        $releaseView = ApplicationAdminReleaseViewDTO::fromRelease($release);
        self::assertSame(0, $releaseView->id);
        self::assertSame('1.2.3', $releaseView->version);
        self::assertSame('stable', $releaseView->channel);
        self::assertSame('https://example.test/app.zip', $releaseView->downloadUrl);
        self::assertSame($release->getPublicationState()->value, $releaseView->publicationState);

        $manifestView = ApplicationAdminManifestViewDTO::fromManifest($manifest);
        self::assertSame('demo.manifest', $manifestView->identifier);
        self::assertSame('1.0', $manifestView->manifestVersion);
        self::assertSame('isolated', $manifestView->sandboxProfile);
        self::assertSame('approved', $manifestView->governanceState);
        self::assertSame(['search'], $manifestView->capabilities);
        self::assertSame(['boot'], $manifestView->runtimeHooks);

        $assignmentView = ApplicationAdminTenantAssignmentViewDTO::fromTenantApplication($assignment);
        self::assertSame(0, $assignmentView->id);
        self::assertSame('tenant-1', $assignmentView->tenantKey);
        self::assertSame('1.2.3', $assignmentView->installedVersion);
        self::assertSame($assignment->getInstallationState()->value, $assignmentView->installationState);
        self::assertTrue($assignmentView->billingActive);
        self::assertTrue($assignmentView->enabled);
        self::assertSame(['healthy' => true], $assignmentView->diagnostics);

        $showView = ApplicationAdminShowViewDTO::fromApplication(
            $application,
            [$releaseView],
            [$manifestView],
            [$assignmentView],
        );
        self::assertSame(0, $showView->id);
        self::assertSame($application->getName(), $showView->nameEntity);
        self::assertSame($application->getPackageName(), $showView->packageName);
        self::assertSame($application->getSlug(), $showView->slug);
        self::assertSame($application->getListingSummary(), $showView->listingSummary);
        self::assertSame($application->getPublicationState()->value, $showView->publicationState);
        self::assertSame([$releaseView], $showView->releases);
        self::assertSame([$manifestView], $showView->manifests);
        self::assertSame([$assignmentView], $showView->tenantAssignments);
    }

    public function testSummaryAndTenantDiagnosticsSerializeExactContracts(): void
    {
        $summary = new ApplicationSummaryDTO(5, 3, 8, 6, 4);
        self::assertSame([
            'applicationsTotal' => 5,
            'applicationsPublished' => 3,
            'tenantAssignmentsTotal' => 8,
            'tenantAssignmentsEnabled' => 6,
            'billingActiveTotal' => 4,
        ], $summary->toArray());

        $checks = new ApplicationTenantDiagnosticsChecksDTO(
            true,
            false,
            'isolated',
            ['catalog.read', 'runtime.execute'],
        );
        self::assertSame([
            'manifest_present' => true,
            'release_present' => false,
            'sandbox_profile' => 'isolated',
            'access_policy_keys' => ['catalog.read', 'runtime.execute'],
        ], $checks->toArray());

        $diagnostics = new ApplicationTenantDiagnosticsDTO(
            'tenant-1',
            'demo-application',
            '1.2.3',
            true,
            false,
            'installed',
            $checks,
            '2026-10-05T19:00:00+00:00',
        );
        self::assertSame([
            'tenantKey' => 'tenant-1',
            'application' => 'demo-application',
            'version' => '1.2.3',
            'enabled' => true,
            'billingActive' => false,
            'installationState' => 'installed',
            'checks' => $checks->toArray(),
            'reportedAt' => '2026-10-05T19:00:00+00:00',
        ], $diagnostics->toArray());
    }
}
