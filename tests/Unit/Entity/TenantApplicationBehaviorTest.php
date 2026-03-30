<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Application;
use App\Entity\TenantApplication;
use PHPUnit\Framework\TestCase;

final class TenantApplicationBehaviorTest extends TestCase
{
    public function testConstructorSetsInstalledStateWhenEnabled(): void
    {
        $tenantApplication = new TenantApplication(
            $this->createApplication(),
            'tenant-alpha',
            '1.0.0',
            true,
            true,
            ['scope' => 'tenant']
        );

        self::assertTrue($tenantApplication->isEnabled());
        self::assertTrue($tenantApplication->isBillingActive());
        self::assertSame('installed', $tenantApplication->getInstallationState()->value);
        self::assertNotNull($tenantApplication->getInstalledAt());
    }

    public function testConstructorSetsAssignedStateWhenDisabled(): void
    {
        $tenantApplication = new TenantApplication(
            $this->createApplication(),
            'tenant-beta',
            '1.0.0',
            false,
            true,
            ['scope' => 'tenant']
        );

        self::assertFalse($tenantApplication->isEnabled());
        self::assertSame('assigned', $tenantApplication->getInstallationState()->value);
        self::assertNull($tenantApplication->getInstalledAt());
    }

    public function testEnableDisableAndDiagnosticsFlow(): void
    {
        $tenantApplication = new TenantApplication(
            $this->createApplication(),
            'tenant-gamma',
            '1.0.0',
            false,
            false,
            ['scope' => 'tenant']
        );

        $tenantApplication->enable();
        self::assertTrue($tenantApplication->isEnabled());
        self::assertSame('installed', $tenantApplication->getInstallationState()->value);
        self::assertNotNull($tenantApplication->getInstalledAt());

        $tenantApplication->disable();
        self::assertFalse($tenantApplication->isEnabled());
        self::assertSame('disabled', $tenantApplication->getInstallationState()->value);

        $tenantApplication->setDiagnostics(['status' => 'ok']);
        self::assertSame(['status' => 'ok'], $tenantApplication->getDiagnostics());
        self::assertNotNull($tenantApplication->getLastCheckedAt());
    }

    private function createApplication(): Application
    {
        return new Application(
            'Tenant Demo Application',
            'tenant-demo-application',
            'applicating/tenant-demo-application',
            'Applicating Labs',
            'Tenant listing summary'
        );
    }
}
