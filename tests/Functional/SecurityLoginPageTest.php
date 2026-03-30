<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SecurityLoginPageTest extends WebTestCase
{
    public function testLoginPageIsPublicAndShowsWorkspaceContext(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();

        self::assertStringContainsString('Application Workspace Login', $content);
        self::assertStringContainsString('Username', $content);
        self::assertStringContainsString('Password', $content);
        self::assertStringContainsString('Sign in', $content);
        self::assertStringContainsString('admin/admin', $content);
        self::assertStringContainsString('manager/manager', $content);
        self::assertStringContainsString('viewer/viewer', $content);
    }

    public function testAnonymousAdminAccessRedirectsToLoginPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/applications');

        self::assertResponseRedirects('/login');
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Application Workspace Login', (string) $client->getResponse()->getContent());
    }
}
