<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\Service;

use App\Applicating\Service\ApplicationHealthService;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;

final class ApplicationHealthServiceTest extends TestCase
{
    public function testBuildHealthReturnsExpectedLivenessPayload(): void
    {
        $connection = $this->createMock(Connection::class);
        $service = new ApplicationHealthService($connection);

        /** @var array{status: string, checks: array{database: array{status: string, message: string}}, generatedAt: string} $payload */
        $payload = $service->buildHealth();

        self::assertSame('ok', $payload['status']);
        self::assertSame('up', $payload['checks']['database']['status']);
        self::assertArrayHasKey('generatedAt', $payload);
    }

    public function testBuildReadinessReturnsNotReadyWhenDatabaseFails(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects(self::once())
            ->method('fetchOne')
            ->with('SELECT 1')
            ->willThrowException(new \RuntimeException('forced-db-failure'));

        $service = new ApplicationHealthService($connection);
        /** @var array{status: string, checks: array{database: array{status: string, message: string}}, generatedAt: string} $payload */
        $payload = $service->buildReadiness();

        self::assertSame('not_ready', $payload['status']);
        self::assertSame('down', $payload['checks']['database']['status']);
        self::assertSame('forced-db-failure', $payload['checks']['database']['message']);
    }
}
