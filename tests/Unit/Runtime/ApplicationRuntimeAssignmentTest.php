<?php

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\Runtime;

use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Entity\ApplicationRuntimeAssignmentEntity;
use App\Applicating\Enum\ApplicationRuntimeMode;
use PHPUnit\Framework\TestCase;

final class ApplicationRuntimeAssignmentTest extends TestCase
{
    public function testConstructorRejectsBlankEnvironment(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Application runtime environment must not be empty.');

        new ApplicationRuntimeAssignmentEntity($this->application(), '   ');
    }

    public function testConstructorNormalizesEnvironmentAndDefaultsToSharedHost(): void
    {
        $assignment = new ApplicationRuntimeAssignmentEntity($this->application(), ' production ');

        self::assertSame('production', $assignment->getEnvironment());
        self::assertSame(ApplicationRuntimeMode::HostShared, $assignment->getRuntimeMode());
    }

    private function application(): ApplicationEntity
    {
        return new ApplicationEntity('One Tasker', 'one_tasker', 'one-tasker/application', 'SmartResponsor', 'One Tasker application.');
    }
}
