<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Application\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ApplicationHealthControllerTest extends WebTestCase
{
    public function testHealthEndpointReturnsLivenessPayload(): void
    {
        $client = static::createClient();
        $client->request('GET', '/health');

        self::assertResponseIsSuccessful();
        /** @var array{status: string, checks: array{database: array{status: string, message: string}}, generatedAt: string} $payload */
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('ok', $payload['status']);
        self::assertSame('up', $payload['checks']['database']['status']);
        self::assertArrayHasKey('generatedAt', $payload);
    }

    public function testReadyEndpointReturnsReadyStatusWhenDatabaseIsAvailable(): void
    {
        $client = static::createClient();
        $client->request('GET', '/ready');

        self::assertResponseStatusCodeSame(200);
        /** @var array{status: string, checks: array{database: array{status: string, message: string}}, generatedAt: string} $payload */
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('ready', $payload['status']);
        self::assertSame('up', $payload['checks']['database']['status']);
        self::assertArrayHasKey('generatedAt', $payload);
    }
}
