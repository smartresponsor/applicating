<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\Builder;

use App\Applicating\Builder\ApplicationAdminViewBuilder;
use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Entity\ApplicationManifestEntity;
use App\Applicating\Entity\ApplicationReleaseEntity;
use App\Applicating\Entity\ApplicationTenantAssignmentEntity;
use PHPUnit\Framework\TestCase;

final class ApplicationAdminViewBuilderTest extends TestCase
{
    public function testBuildIndexRowsPreservesOrderAndProjectsRelationCounts(): void
    {
        $first = $this->application('First Application', 'first-application');
        $second = $this->application('Second Application', 'second-application');

        $first->addRelease(new ApplicationReleaseEntity(
            $first,
            '1.0.0',
            'stable',
            'abc123',
            'https://example.test/first.zip',
            'First release.',
        ));
        $first->addTenantApplication(new ApplicationTenantAssignmentEntity(
            $first,
            'tenant-a',
            '1.0.0',
            true,
            true,
            [],
        ));

        $rows = (new ApplicationAdminViewBuilder())->buildIndexRows([$first, $second]);

        self::assertCount(2, $rows);
        self::assertSame('First Application', $rows[0]->nameEntity);
        self::assertSame('first-application', $rows[0]->slug);
        self::assertSame(1, $rows[0]->releaseCount);
        self::assertSame(1, $rows[0]->tenantAssignmentCount);
        self::assertSame('Second Application', $rows[1]->nameEntity);
        self::assertSame(0, $rows[1]->releaseCount);
        self::assertSame(0, $rows[1]->tenantAssignmentCount);
    }

    public function testBuildShowViewProjectsLifecycleRelations(): void
    {
        $application = $this->application('Demo Application', 'demo-application');
        $release = new ApplicationReleaseEntity(
            $application,
            '2.0.0',
            'stable',
            'def456',
            'https://example.test/demo.zip',
            'Major release.',
        );
        $manifest = new ApplicationManifestEntity(
            $application,
            '2.0',
            'demo.manifest',
            ['reporting'],
            ['catalog.read'],
            ['boot'],
            'restricted',
            'approved',
            ['name' => 'demo'],
        );
        $assignment = new ApplicationTenantAssignmentEntity(
            $application,
            'tenant-a',
            '2.0.0',
            true,
            true,
            ['scope' => 'read'],
        );
        $assignment->setDiagnostics(['healthy' => true]);

        $application->addRelease($release);
        $application->addManifest($manifest);
        $application->addTenantApplication($assignment);

        $view = (new ApplicationAdminViewBuilder())->buildShowView($application);

        self::assertSame('Demo Application', $view->nameEntity);
        self::assertSame('demo-application', $view->slug);
        self::assertCount(1, $view->releases);
        self::assertSame('2.0.0', $view->releases[0]->version);
        self::assertCount(1, $view->manifests);
        self::assertSame('demo.manifest', $view->manifests[0]->identifier);
        self::assertCount(1, $view->tenantAssignments);
        self::assertSame('tenant-a', $view->tenantAssignments[0]->tenantKey);
        self::assertSame(['healthy' => true], $view->tenantAssignments[0]->diagnostics);
    }

    private function application(string $name, string $slug): ApplicationEntity
    {
        return new ApplicationEntity(
            $name,
            $slug,
            'vendor/'.$slug,
            'Developer',
            'Application summary.',
        );
    }
}
