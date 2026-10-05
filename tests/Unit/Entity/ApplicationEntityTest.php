<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\Entity;

use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Entity\ApplicationManifestEntity;
use App\Applicating\Entity\ApplicationReleaseEntity;
use App\Applicating\Entity\ApplicationTenantAssignmentEntity;
use App\Applicating\Enum\ApplicationAccessLevel;
use App\Applicating\Enum\ApplicationPublicationState;
use PHPUnit\Framework\TestCase;

final class ApplicationEntityTest extends TestCase
{
    public function testMutableMetadataAndLifecycleState(): void
    {
        $application = $this->application();

        self::assertSame('Demo Application', $application->getName());
        self::assertSame('demo-application', $application->getSlug());
        self::assertSame(ApplicationPublicationState::Draft, $application->getPublicationState());
        self::assertSame(ApplicationAccessLevel::Public, $application->getAccessLevel());
        self::assertNull($application->getBillingCode());
        self::assertSame('default', $application->getSandboxProfile());
        self::assertFalse($application->isEnabledByDefault());

        $application->rename('Renamed Application');
        $application->changeSlug('renamed-application');
        $application->changePackageName('renamed/application');
        $application->changeDeveloperName('Renamed Developer');
        $application->changeListingSummary('Renamed summary.');
        $application->changeAccessLevel(ApplicationAccessLevel::TenantRestricted);
        $application->changeBillingCode('BILL-42');
        $application->changeSandboxProfile('isolated');
        $application->changeEnabledByDefault(true);

        self::assertSame('Renamed Application', $application->getName());
        self::assertSame('renamed-application', $application->getSlug());
        self::assertSame('renamed/application', $application->getPackageName());
        self::assertSame('Renamed Developer', $application->getDeveloperName());
        self::assertSame('Renamed summary.', $application->getListingSummary());
        self::assertSame(ApplicationAccessLevel::TenantRestricted, $application->getAccessLevel());
        self::assertSame('BILL-42', $application->getBillingCode());
        self::assertSame('isolated', $application->getSandboxProfile());
        self::assertTrue($application->isEnabledByDefault());

        $application->markForModeration();
        self::assertSame(ApplicationPublicationState::Moderation, $application->getPublicationState());
        $application->publish();
        self::assertSame(ApplicationPublicationState::Published, $application->getPublicationState());
        $application->suspend();
        self::assertSame(ApplicationPublicationState::Suspended, $application->getPublicationState());
    }

    public function testRelationsAreAddedOnlyOnce(): void
    {
        $application = $this->application();
        $release = new ApplicationReleaseEntity($application, '1.0.0', 'stable', 'abc123', 'https://example.test/release.zip', 'Initial.');
        $manifest = new ApplicationManifestEntity($application, '1.0', 'demo.manifest', [], [], [], 'default', 'approved', []);
        $assignment = new ApplicationTenantAssignmentEntity($application, 'tenant-1', '1.0.0', true, true, []);

        $application->addRelease($release);
        $application->addRelease($release);
        $application->addManifest($manifest);
        $application->addManifest($manifest);
        $application->addTenantApplication($assignment);
        $application->addTenantApplication($assignment);

        self::assertCount(1, $application->getReleases());
        self::assertCount(1, $application->getManifests());
        self::assertCount(1, $application->getTenantApplications());
        self::assertInstanceOf(\DateTimeImmutable::class, $application->getUpdatedAt());

        $application->onCreate();
        $application->onUpdate();

        self::assertInstanceOf(\DateTimeImmutable::class, $application->getUpdatedAt());
    }

    private function application(): ApplicationEntity
    {
        return new ApplicationEntity(
            'Demo Application',
            'demo-application',
            'demo/application',
            'Demo Developer',
            'Demo application summary.',
        );
    }
}
