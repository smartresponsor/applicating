<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ApplicationSecurityTest extends WebTestCase
{
    public function testAnonymousUserIsRedirectedToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/applications');
        self::assertResponseRedirects('/login');
    }

    public function testViewerCannotAccessManagerOnlyApplicationRoutes(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        $client->submitForm('Sign in', [
            '_username' => 'viewer',
            '_password' => 'viewer',
        ]);

        $client->request('GET', '/admin/applications/new');
        self::assertResponseStatusCodeSame(403);

        $client->request('GET', '/api/admin/applications');
        self::assertResponseStatusCodeSame(403);
    }
}
