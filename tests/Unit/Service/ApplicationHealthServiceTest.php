<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\Service;

use App\Applicating\RepositoryInterface\ApplicationRepositoryInterface;
use App\Applicating\Service\ApplicationHealthService;
use PHPUnit\Framework\TestCase;

final class ApplicationHealthServiceTest extends TestCase
{
    public function testBuildHealthReturnsExpectedLivenessPayload(): void
    {
        $repository = $this->createMock(ApplicationRepositoryInterface::class);
        $service = new ApplicationHealthService($repository);

        /** @var array{status: string, checks: array{database: array{status: string, message: string}}, generatedAt: string} $payload */
        $payload = $service->buildHealth();

        self::assertSame('ok', $payload['status']);
        self::assertSame('up', $payload['checks']['database']['status']);
        self::assertArrayHasKey('generatedAt', $payload);
    }

    public function testBuildReadinessReturnsNotReadyWhenDatabaseFails(): void
    {
        $repository = $this->createMock(ApplicationRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('countAllApplications')
            ->willThrowException(new \RuntimeException('forced-db-failure'));

        $service = new ApplicationHealthService($repository);
        /** @var array{status: string, checks: array{database: array{status: string, message: string}}, generatedAt: string} $payload */
        $payload = $service->buildReadiness();

        self::assertSame('not_ready', $payload['status']);
        self::assertSame('down', $payload['checks']['database']['status']);
        self::assertSame('forced-db-failure', $payload['checks']['database']['message']);
    }
}
