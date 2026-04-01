<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SecurityLogoutFlowTest extends WebTestCase
{
    public function testAuthenticatedViewerCanLogoutAndLosesAdminAccess(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Sign in')->form([
            '_username' => 'viewer',
            '_password' => 'viewer',
        ]);
        $client->submit($form);

        self::assertResponseRedirects('/admin/applications');
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Application Lifecycle Management', (string) $client->getResponse()->getContent());

        $client->request('GET', '/logout');
        self::assertResponseRedirects('/login');
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Application Workspace Login', (string) $client->getResponse()->getContent());

        $client->request('GET', '/admin/applications');
        self::assertResponseRedirects('/login');
    }
}
