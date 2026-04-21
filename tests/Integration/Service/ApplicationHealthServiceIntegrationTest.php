<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\ServiceInterface\ApplicationHealthServiceInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ApplicationHealthServiceIntegrationTest extends KernelTestCase
{
    public function testBuildReadinessReturnsReadyWithConfiguredConnection(): void
    {
        self::bootKernel();

        $service = static::getContainer()->get(ApplicationHealthServiceInterface::class);
        $payload = $service->buildReadiness();

        self::assertSame('ready', $payload['status']);
        self::assertSame('up', $payload['checks']['database']['status']);
        self::assertArrayHasKey('generatedAt', $payload);
    }
}
