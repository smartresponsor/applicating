<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\EventSubscriber;

use App\Applicating\Entity\ApplicationUserEntity;
use App\Applicating\EventSubscriber\ApplicationUserLoginSubscriber;
use App\Applicating\RepositoryInterface\ApplicationUserRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final class ApplicationUserLoginSubscriberTest extends TestCase
{
    public function testSubscribedEventsRegistersLoginSuccessHandler(): void
    {
        self::assertSame(
            [LoginSuccessEvent::class => 'onLoginSuccess'],
            ApplicationUserLoginSubscriber::getSubscribedEvents(),
        );
    }

    public function testLoginSuccessIgnoresUnsupportedUser(): void
    {
        $repository = $this->createMock(ApplicationUserRepositoryInterface::class);
        $repository->expects(self::never())->method('save');

        $event = $this->createMock(LoginSuccessEvent::class);
        $event->method('getUser')->willReturn($this->createStub(UserInterface::class));

        $subscriber = new ApplicationUserLoginSubscriber($repository);
        $subscriber->onLoginSuccess($event);
    }

    public function testLoginSuccessMarksApplicationUserAndPersistsIt(): void
    {
        $user = new ApplicationUserEntity('manager', 'Manager');
        self::assertNull($user->getLastLoginAt());

        $repository = $this->createMock(ApplicationUserRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('save')
            ->with(self::identicalTo($user));

        $event = $this->createMock(LoginSuccessEvent::class);
        $event->method('getUser')->willReturn($user);

        $subscriber = new ApplicationUserLoginSubscriber($repository);
        $subscriber->onLoginSuccess($event);

        self::assertInstanceOf(\DateTimeImmutable::class, $user->getLastLoginAt());
    }
}
