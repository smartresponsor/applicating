<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\Voter;

use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Entity\ApplicationTenantAssignmentEntity;
use App\Applicating\Voter\ApplicationVoter;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class ApplicationVoterTest extends TestCase
{
    public function testUnsupportedAttributeAbstains(): void
    {
        $result = $this->voter([])->vote(
            $this->createStub(TokenInterface::class),
            $this->application(),
            ['UNSUPPORTED'],
        );

        self::assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function testAdministratorOverrideGrantsEdit(): void
    {
        $result = $this->voter(['ROLE_APPLICATION_ADMIN' => true])->vote(
            $this->createStub(TokenInterface::class),
            $this->application(),
            [ApplicationVoter::EDIT],
        );

        self::assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testManagerCanEditApplication(): void
    {
        $result = $this->voter(['ROLE_APPLICATION_MANAGER' => true])->vote(
            $this->createStub(TokenInterface::class),
            $this->application(),
            [ApplicationVoter::EDIT],
        );

        self::assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testSuspendedApplicationCannotBePublishedByManager(): void
    {
        $application = $this->application();
        $application->suspend();

        $result = $this->voter(['ROLE_APPLICATION_MANAGER' => true])->vote(
            $this->createStub(TokenInterface::class),
            $application,
            [ApplicationVoter::PUBLISH],
        );

        self::assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testManagerCanAssignPublishedApplication(): void
    {
        $application = $this->application();
        $application->publish();

        $result = $this->voter(['ROLE_APPLICATION_MANAGER' => true])->vote(
            $this->createStub(TokenInterface::class),
            $application,
            [ApplicationVoter::ASSIGN],
        );

        self::assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testManagerCannotAssignDraftApplication(): void
    {
        $result = $this->voter(['ROLE_APPLICATION_MANAGER' => true])->vote(
            $this->createStub(TokenInterface::class),
            $this->application(),
            [ApplicationVoter::ASSIGN],
        );

        self::assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testManagerCanToggleTenantAssignment(): void
    {
        $assignment = new ApplicationTenantAssignmentEntity(
            $this->application(),
            'tenant-a',
            '1.0.0',
            true,
            true,
            [],
        );

        $result = $this->voter(['ROLE_APPLICATION_MANAGER' => true])->vote(
            $this->createStub(TokenInterface::class),
            $assignment,
            [ApplicationVoter::TOGGLE],
        );

        self::assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testNonToggleTenantAssignmentActionIsDenied(): void
    {
        $assignment = new ApplicationTenantAssignmentEntity(
            $this->application(),
            'tenant-a',
            '1.0.0',
            true,
            true,
            [],
        );

        $result = $this->voter(['ROLE_APPLICATION_MANAGER' => true])->vote(
            $this->createStub(TokenInterface::class),
            $assignment,
            [ApplicationVoter::EDIT],
        );

        self::assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    /** @param array<string, bool> $grants */
    private function voter(array $grants): ApplicationVoter
    {
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker
            ->method('isGranted')
            ->willReturnCallback(static fn (string $attribute): bool => $grants[$attribute] ?? false);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('get')
            ->with('security.authorization_checker')
            ->willReturn($authorizationChecker);

        return new ApplicationVoter(new Security($container));
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
