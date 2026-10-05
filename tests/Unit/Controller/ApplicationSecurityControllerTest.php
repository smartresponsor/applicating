<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\Controller;

use App\Applicating\Controller\ApplicationSecurityController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class ApplicationSecurityControllerTest extends TestCase
{
    public function testLoginReturnsSecurityPayloadWithStringRoute(): void
    {
        $request = new Request();
        $request->attributes->set('_route', 'applicating_security_login');

        $authenticationUtils = $this->createMock(AuthenticationUtils::class);
        $authenticationUtils->method('getLastUsername')->willReturn('user@example.test');
        $authenticationUtils->method('getLastAuthenticationError')->willReturn(null);

        /** @var array{_view: array{surface:string,operation:string,component:string},locations: array{body: array{last_username:string,error:mixed,title:string}},data: array{last_username:string,error:mixed,title:string},meta: array{source:string,route:string}} $result */
        $result = (new ApplicationSecurityController())->login($request, $authenticationUtils);

        self::assertSame('security', $result['_view']['surface']);
        self::assertSame('login', $result['_view']['operation']);
        self::assertSame('Applicating', $result['_view']['component']);
        self::assertSame('user@example.test', $result['data']['last_username']);
        self::assertNull($result['data']['error']);
        self::assertSame('Sign in', $result['data']['title']);
        self::assertSame($result['data'], $result['locations']['body']);
        self::assertSame('applicating_security_login', $result['meta']['source']);
        self::assertSame('applicating_security_login', $result['meta']['route']);
    }

    public function testLoginUsesEmptyRouteForNonStringRouteAttribute(): void
    {
        $request = new Request();
        $request->attributes->set('_route', 42);

        $authenticationUtils = $this->createMock(AuthenticationUtils::class);
        $authenticationUtils->method('getLastUsername')->willReturn('');
        $authenticationUtils->method('getLastAuthenticationError')->willReturn(null);

        /** @var array{meta: array{route:string}} $result */
        $result = (new ApplicationSecurityController())->login($request, $authenticationUtils);

        self::assertSame('', $result['meta']['route']);
    }

    public function testLogoutThrowsBecauseSymfonyOwnsTheLogoutFlow(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Logout is managed by Symfony security.');

        (new ApplicationSecurityController())->logout();
    }
}
