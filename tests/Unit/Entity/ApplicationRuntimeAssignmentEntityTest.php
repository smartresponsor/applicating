<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\Entity;

use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Entity\ApplicationRuntimeAssignmentEntity;
use App\Applicating\Enum\ApplicationRuntimeMode;
use PHPUnit\Framework\TestCase;

final class ApplicationRuntimeAssignmentEntityTest extends TestCase
{
    public function testConstructorNormalizesEnvironmentAndUsesDefaultMode(): void
    {
        $application = $this->application();
        $assignment = new ApplicationRuntimeAssignmentEntity($application, '  production  ');

        self::assertNull($assignment->getId());
        self::assertSame($application, $assignment->getApplication());
        self::assertSame('production', $assignment->getEnvironment());
        self::assertSame(ApplicationRuntimeMode::HostShared, $assignment->getRuntimeMode());
        self::assertSame($assignment->getCreatedAt(), $assignment->getUpdatedAt());
    }

    public function testConstructorRejectsBlankEnvironment(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Application runtime environment must not be empty.');

        new ApplicationRuntimeAssignmentEntity($this->application(), '   ');
    }

    public function testChangingModeUpdatesTimestampOnlyWhenModeChanges(): void
    {
        $assignment = new ApplicationRuntimeAssignmentEntity(
            $this->application(),
            'staging',
            ApplicationRuntimeMode::HostShared,
        );
        $initialUpdatedAt = $assignment->getUpdatedAt();

        $assignment->changeRuntimeMode(ApplicationRuntimeMode::HostShared);

        self::assertSame($initialUpdatedAt, $assignment->getUpdatedAt());

        usleep(1000);
        $assignment->changeRuntimeMode(ApplicationRuntimeMode::CustomDomain);

        self::assertSame(ApplicationRuntimeMode::CustomDomain, $assignment->getRuntimeMode());
        self::assertGreaterThanOrEqual($initialUpdatedAt, $assignment->getUpdatedAt());
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
