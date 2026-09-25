<?php

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\Runtime;

use App\Applicating\Entity\ApplicationEntity;
use App\Applicating\Entity\ApplicationRuntimeAssignmentEntity;
use App\Applicating\Enum\ApplicationRuntimeMode;
use App\Applicating\RepositoryInterface\ApplicationRuntimeAssignmentRepositoryInterface;
use App\Applicating\Resolver\ApplicationRuntimeAssignmentResolver;
use PHPUnit\Framework\TestCase;

final class ApplicationRuntimeAssignmentResolverTest extends TestCase
{
    public function testMissingAssignmentDefaultsToSharedHost(): void
    {
        $repository = $this->createMock(ApplicationRuntimeAssignmentRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('findOneForApplicationAndEnvironment')
            ->with('one_tasker', 'production')
            ->willReturn(null);

        $resolver = new ApplicationRuntimeAssignmentResolver($repository);

        self::assertSame(ApplicationRuntimeMode::HostShared, $resolver->resolveMode('one_tasker', 'production'));
    }

    public function testExistingAssignmentReturnsPersistedMode(): void
    {
        $application = new ApplicationEntity('One Tasker', 'one_tasker', 'one-tasker/application', 'SmartResponsor', 'One Tasker application.');
        $assignment = new ApplicationRuntimeAssignmentEntity($application, 'production', ApplicationRuntimeMode::CustomDomain);
        $repository = $this->createMock(ApplicationRuntimeAssignmentRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('findOneForApplicationAndEnvironment')
            ->with('one_tasker', 'production')
            ->willReturn($assignment);

        $resolver = new ApplicationRuntimeAssignmentResolver($repository);

        self::assertSame(ApplicationRuntimeMode::CustomDomain, $resolver->resolveMode('one_tasker', 'production'));
    }
}
