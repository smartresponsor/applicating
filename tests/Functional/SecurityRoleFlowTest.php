<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SecurityRoleFlowTest extends WebTestCase
{
    public function testViewerLoginCannotAccessManagerSurfaces(): void
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

        $client->request('GET', '/admin/applications/new');
        self::assertResponseStatusCodeSame(403);

        $client->request('GET', '/api/admin/applications');
        self::assertResponseStatusCodeSame(403);
    }

    public function testManagerLoginCanAccessManagerSurfaces(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Sign in')->form([
            '_username' => 'manager',
            '_password' => 'manager',
        ]);
        $client->submit($form);

        self::assertResponseRedirects('/admin/applications');
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        $client->request('GET', '/admin/applications/new');
        self::assertResponseIsSuccessful();

        $client->request('GET', '/api/admin/applications');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('application/json', (string) $client->getResponse()->headers->get('content-type'));
    }
}
