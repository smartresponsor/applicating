<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Application\Tests\Integration\Service;

use App\Application\ServiceInterface\ApplicationHealthServiceInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ApplicationHealthServiceIntegrationTest extends KernelTestCase
{
    public function testBuildReadinessReturnsReadyWithConfiguredConnection(): void
    {
        self::bootKernel();

        /** @var ApplicationHealthServiceInterface $service */
        $service = static::getContainer()->get(ApplicationHealthServiceInterface::class);
        /** @var array{status: string, checks: array{database: array{status: string, message: string}}, generatedAt: string} $payload */
        $payload = $service->buildReadiness();

        self::assertSame('ready', $payload['status']);
        self::assertSame('up', $payload['checks']['database']['status']);
        self::assertArrayHasKey('generatedAt', $payload);
    }
}
