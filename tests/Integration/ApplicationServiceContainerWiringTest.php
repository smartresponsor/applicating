<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Service\ApplicationDiagnosticsService;
use App\Service\ApplicationLifecycleService;
use App\Service\ApplicationManifestService;
use App\Service\ApplicationReportService;
use App\ServiceInterface\ApplicationDiagnosticsServiceInterface;
use App\ServiceInterface\ApplicationLifecycleServiceInterface;
use App\ServiceInterface\ApplicationManifestServiceInterface;
use App\ServiceInterface\ApplicationReportServiceInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ApplicationServiceContainerWiringTest extends KernelTestCase
{
    public function testContainerResolvesLifecycleServiceInterface(): void
    {
        self::bootKernel();

        $service = static::getContainer()->get(ApplicationLifecycleServiceInterface::class);

        self::assertInstanceOf(ApplicationLifecycleService::class, $service);
    }

    public function testContainerResolvesManifestServiceInterface(): void
    {
        self::bootKernel();

        $service = static::getContainer()->get(ApplicationManifestServiceInterface::class);

        self::assertInstanceOf(ApplicationManifestService::class, $service);
    }

    public function testContainerResolvesDiagnosticsServiceInterface(): void
    {
        self::bootKernel();

        $service = static::getContainer()->get(ApplicationDiagnosticsServiceInterface::class);

        self::assertInstanceOf(ApplicationDiagnosticsService::class, $service);
    }

    public function testContainerResolvesReportServiceInterface(): void
    {
        self::bootKernel();

        $service = static::getContainer()->get(ApplicationReportServiceInterface::class);

        self::assertInstanceOf(ApplicationReportService::class, $service);
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }
}
