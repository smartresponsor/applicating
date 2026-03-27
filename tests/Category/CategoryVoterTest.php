<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Security\CategoryVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\InMemoryUser;

final class CategoryVoterTest extends TestCase
{
    public function testAdminCanPublish(): void
    {
        $voter = new CategoryVoter();
        $user = new InMemoryUser('admin', null, ['ROLE_ADMIN']);
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $res = $voter->vote($token, null, [CategoryVoter::PUBLISH]);
        self::assertSame(VoterInterface::ACCESS_GRANTED, $res);
    }
}
