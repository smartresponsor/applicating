<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\Entity;

use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Entity\ApplicationTenantAssignmentEntity;
use App\Applicating\Enum\ApplicationInstallationState;
use PHPUnit\Framework\TestCase;

final class ApplicationTenantAssignmentEntityTest extends TestCase
{
    public function testDisabledAssignmentStartsAssignedWithoutInstallationTimestamp(): void
    {
        $application = $this->application();
        $assignment = new ApplicationTenantAssignmentEntity(
            $application,
            'tenant-a',
            '1.0.0',
            false,
            true,
            ['scope' => 'read'],
        );

        self::assertNull($assignment->getId());
        self::assertSame($application, $assignment->getApplication());
        self::assertSame('tenant-a', $assignment->getTenantKey());
        self::assertSame('1.0.0', $assignment->getInstalledVersion());
        self::assertSame(ApplicationInstallationState::Assigned, $assignment->getInstallationState());
        self::assertFalse($assignment->isEnabled());
        self::assertTrue($assignment->isBillingActive());
        self::assertSame(['scope' => 'read'], $assignment->getAccessPolicy());
        self::assertSame([], $assignment->getDiagnostics());
        self::assertInstanceOf(\DateTimeImmutable::class, $assignment->getAssignedAt());
        self::assertNull($assignment->getInstalledAt());
        self::assertNull($assignment->getLastCheckedAt());
    }

    public function testEnabledAssignmentStartsInstalled(): void
    {
        $assignment = new ApplicationTenantAssignmentEntity(
            $this->application(),
            'tenant-a',
            '1.0.0',
            true,
            false,
            [],
        );

        self::assertSame(ApplicationInstallationState::Installed, $assignment->getInstallationState());
        self::assertTrue($assignment->isEnabled());
        self::assertFalse($assignment->isBillingActive());
        self::assertInstanceOf(\DateTimeImmutable::class, $assignment->getInstalledAt());
    }

    public function testUpdateAssignmentCanEnableAndReplaceMutableFields(): void
    {
        $assignment = new ApplicationTenantAssignmentEntity(
            $this->application(),
            'tenant-a',
            '1.0.0',
            false,
            false,
            [],
        );

        $assignment->updateAssignment('2.0.0', true, true, ['scope' => 'write']);

        self::assertSame('2.0.0', $assignment->getInstalledVersion());
        self::assertSame(ApplicationInstallationState::Installed, $assignment->getInstallationState());
        self::assertTrue($assignment->isEnabled());
        self::assertTrue($assignment->isBillingActive());
        self::assertSame(['scope' => 'write'], $assignment->getAccessPolicy());
        self::assertInstanceOf(\DateTimeImmutable::class, $assignment->getInstalledAt());
    }

    public function testUpdateAssignmentCanDisableExistingInstallation(): void
    {
        $assignment = new ApplicationTenantAssignmentEntity(
            $this->application(),
            'tenant-a',
            '1.0.0',
            true,
            true,
            [],
        );
        $installedAt = $assignment->getInstalledAt();

        $assignment->updateAssignment('2.0.0', false, false, ['scope' => 'read']);

        self::assertSame(ApplicationInstallationState::Disabled, $assignment->getInstallationState());
        self::assertFalse($assignment->isEnabled());
        self::assertFalse($assignment->isBillingActive());
        self::assertSame(['scope' => 'read'], $assignment->getAccessPolicy());
        self::assertSame($installedAt, $assignment->getInstalledAt());
    }

    public function testEnablePreservesFirstInstallationTimestamp(): void
    {
        $assignment = new ApplicationTenantAssignmentEntity(
            $this->application(),
            'tenant-a',
            '1.0.0',
            true,
            true,
            [],
        );
        $installedAt = $assignment->getInstalledAt();

        $assignment->disable();
        $assignment->enable();

        self::assertSame(ApplicationInstallationState::Installed, $assignment->getInstallationState());
        self::assertTrue($assignment->isEnabled());
        self::assertSame($installedAt, $assignment->getInstalledAt());
    }

    public function testDiagnosticsRecordLastCheckTimestamp(): void
    {
        $assignment = new ApplicationTenantAssignmentEntity(
            $this->application(),
            'tenant-a',
            '1.0.0',
            false,
            false,
            [],
        );

        $assignment->setDiagnostics(['healthy' => true]);

        self::assertSame(['healthy' => true], $assignment->getDiagnostics());
        self::assertInstanceOf(\DateTimeImmutable::class, $assignment->getLastCheckedAt());
    }

    private function application(): ApplicationEntity
    {
        return new ApplicationEntity(
            'Application',
            'application',
            'vendor/application',
            'Developer',
            'Application summary',
        );
    }
}
