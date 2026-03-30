<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Application;
use App\Entity\ApplicationManifest;
use App\Entity\ApplicationRelease;
use App\Entity\TenantApplication;
use App\Service\ApplicationDiagnosticsService;
use PHPUnit\Framework\TestCase;

final class ApplicationDiagnosticsServiceTest extends TestCase
{
    public function testBuildTenantDiagnosticsContainsExpectedKeys(): void
    {
        $application = new Application(
            'Diagnostics Demo',
            'diagnostics-demo',
            'applicating/diagnostics-demo',
            'Applicating Labs',
            'Diagnostics listing summary'
        );

        $manifest = new ApplicationManifest(
            $application,
            '1.0.0',
            'io.applicating.diagnostics.demo',
            ['catalog'],
            ['tenant:read'],
            ['bootstrap'],
            'default',
            'approved',
            ['identifier' => 'io.applicating.diagnostics.demo']
        );
        $application->addManifest($manifest);

        $release = new ApplicationRelease(
            $application,
            '1.0.0',
            'stable',
            hash('sha256', 'diagnostics-release'),
            'https://downloads.example.test/diagnostics-demo/1.0.0.zip',
            'Diagnostics release notes'
        );
        $application->addRelease($release);

        $tenantApplication = new TenantApplication(
            $application,
            'tenant-diagnostics',
            '1.0.0',
            true,
            true,
            ['scope' => 'tenant']
        );

        $service = new ApplicationDiagnosticsService();
        $diagnostics = $service->buildTenantDiagnostics($tenantApplication);

        self::assertSame('tenant-diagnostics', $diagnostics['tenantKey']);
        self::assertSame('diagnostics-demo', $diagnostics['application']);
        self::assertSame('1.0.0', $diagnostics['version']);
        self::assertTrue($diagnostics['enabled']);
        self::assertTrue($diagnostics['billingActive']);
        self::assertArrayHasKey('checks', $diagnostics);
        self::assertTrue($diagnostics['checks']['manifest_present']);
        self::assertTrue($diagnostics['checks']['release_present']);
        self::assertSame('default', $diagnostics['checks']['sandbox_profile']);
        self::assertSame(['scope'], $diagnostics['checks']['access_policy_keys']);
        self::assertArrayHasKey('reportedAt', $diagnostics);
    }
}
