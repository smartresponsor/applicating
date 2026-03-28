<?php

declare(strict_types=1);

namespace App\Tests\Panther;

use Symfony\Component\Panther\PantherTestCase;

final class ApplicationManagementPantherTest extends PantherTestCase
{
    public function testLoginPageContainsApplicationHeading(): void
    {
        $client = static::createHttpBrowserClient();
        $client->request('GET', '/login');

        self::assertSelectorTextContains('h1', 'Application Workspace Login');
    }
}
