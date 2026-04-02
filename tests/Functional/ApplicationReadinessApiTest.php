<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ApplicationReadinessApiTest extends WebTestCase
{
    public function testReadinessEndpointReturnsJson(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/admin/applications/non-existing/readiness');

        self::assertResponseIsSuccessful();
        self::assertJson($client->getResponse()->getContent() ?? '');
    }
}
