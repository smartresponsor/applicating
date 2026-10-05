<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\Entity;

use App\Applicating\Entity\ApplicationUserEntity;
use PHPUnit\Framework\TestCase;

final class ApplicationUserEntityTest extends TestCase
{
    public function testIdentityAndProfileFieldsCanBeChanged(): void
    {
        $user = new ApplicationUserEntity('manager', 'Manager');

        self::assertNull($user->getId());
        self::assertSame('manager', $user->getUserIdentifier());
        self::assertSame('Manager', $user->getDisplayName());
        self::assertNull($user->getEmail());

        $user->renameIdentifier('operator');
        $user->renameDisplayName('Operator');
        $user->changeEmail('operator@example.test');

        self::assertSame('operator', $user->getUserIdentifier());
        self::assertSame('Operator', $user->getDisplayName());
        self::assertSame('operator@example.test', $user->getEmail());
    }

    public function testRolesAreDeduplicatedAndSorted(): void
    {
        $user = new ApplicationUserEntity('manager', 'Manager');

        self::assertSame([], $user->getRoles());

        $user->changeRoles(['ROLE_USER', 'ROLE_APPLICATION_ADMIN', 'ROLE_USER']);

        self::assertSame(['ROLE_APPLICATION_ADMIN', 'ROLE_USER'], $user->getRoles());
    }

    public function testAuthenticationFieldsAndActivityStateCanBeChanged(): void
    {
        $user = new ApplicationUserEntity('manager', 'Manager');

        self::assertNull($user->getPassword());
        self::assertSame('local', $user->getAuthSource());
        self::assertNull($user->getExternalSubject());
        self::assertTrue($user->isActive());

        $user->changePassword('hashed-password');
        $user->changeAuthSource('oidc');
        $user->changeExternalSubject('subject-123');
        $user->deactivate();
        $user->eraseCredentials();

        self::assertSame('hashed-password', $user->getPassword());
        self::assertSame('oidc', $user->getAuthSource());
        self::assertSame('subject-123', $user->getExternalSubject());
        self::assertFalse($user->isActive());

        $user->activate();

        self::assertTrue($user->isActive());
    }

    public function testLoginAndLifecycleTimestampsAreMaintained(): void
    {
        $user = new ApplicationUserEntity('manager', 'Manager');
        $createdAt = $user->getCreatedAt();
        $updatedAt = $user->getUpdatedAt();

        self::assertNull($user->getLastLoginAt());
        self::assertInstanceOf(\DateTimeImmutable::class, $createdAt);
        self::assertInstanceOf(\DateTimeImmutable::class, $updatedAt);

        $loggedAt = new \DateTimeImmutable('2026-10-04T12:00:00+00:00');
        $user->markLogin($loggedAt);

        self::assertSame($loggedAt, $user->getLastLoginAt());

        $user->onCreate();
        self::assertSame($user->getCreatedAt(), $user->getUpdatedAt());

        $beforeUpdate = $user->getUpdatedAt();
        usleep(1000);
        $user->onUpdate();

        self::assertGreaterThanOrEqual($beforeUpdate, $user->getUpdatedAt());

        $user->markLogin();
        self::assertInstanceOf(\DateTimeImmutable::class, $user->getLastLoginAt());
    }
}
