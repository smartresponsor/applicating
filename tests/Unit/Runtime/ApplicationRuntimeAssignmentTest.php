<?php

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\Runtime;

use App\Applicating\Entity\Application;
use App\Applicating\Entity\ApplicationRuntimeAssignment;
use App\Applicating\Enum\ApplicationRuntimeMode;
use PHPUnit\Framework\TestCase;

final class ApplicationRuntimeAssignmentTest extends TestCase
{
    public function testConstructorRejectsBlankEnvironment(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Application runtime environment must not be empty.');

        new ApplicationRuntimeAssignment($this->application(), '   ');
    }

    public function testConstructorNormalizesEnvironmentAndDefaultsToSharedHost(): void
    {
        $assignment = new ApplicationRuntimeAssignment($this->application(), ' production ');

        self::assertSame('production', $assignment->getEnvironment());
        self::assertSame(ApplicationRuntimeMode::HostShared, $assignment->getRuntimeMode());
    }

    private function application(): Application
    {
        return new Application('One Tasker', 'one_tasker', 'one-tasker/application', 'SmartResponsor', 'One Tasker application.');
    }
}
