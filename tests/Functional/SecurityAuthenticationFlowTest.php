<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SecurityAuthenticationFlowTest extends WebTestCase
{
    public function testViewerCanAuthenticateThroughLoginForm(): void
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
    }

    public function testInvalidCredentialsShowAuthenticationError(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Sign in')->form([
            '_username' => 'viewer',
            '_password' => 'wrong-password',
        ]);
        $client->submit($form);

        self::assertResponseRedirects('/login');
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Invalid credentials.', (string) $client->getResponse()->getContent());
    }
}
