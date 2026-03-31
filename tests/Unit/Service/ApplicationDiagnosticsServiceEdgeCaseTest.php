<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Application;
use App\Entity\TenantApplication;
use App\Service\ApplicationDiagnosticsService;
use PHPUnit\Framework\TestCase;

final class ApplicationDiagnosticsServiceEdgeCaseTest extends TestCase
{
    public function testBuildTenantDiagnosticsMarksMissingManifestAndRelease(): void
    {
        $application = new Application(
            'Diagnostics Edge Application',
            'diagnostics-edge-application',
            'applicating/diagnostics-edge-application',
            'Applicating Labs',
            'Diagnostics edge summary'
        );
        $application->changeSandboxProfile('restricted');

        $tenantApplication = new TenantApplication(
            $application,
            'tenant-diagnostics-edge',
            '1.0.0',
            false,
            false,
            []
        );

        $service = new ApplicationDiagnosticsService();
        $diagnostics = $service->buildTenantDiagnostics($tenantApplication);

        self::assertSame('tenant-diagnostics-edge', $diagnostics['tenantKey']);
        self::assertSame('diagnostics-edge-application', $diagnostics['application']);
        self::assertSame('1.0.0', $diagnostics['version']);
        self::assertFalse($diagnostics['enabled']);
        self::assertFalse($diagnostics['billingActive']);
        self::assertSame('assigned', $diagnostics['installationState']);
        self::assertFalse($diagnostics['checks']['manifest_present']);
        self::assertFalse($diagnostics['checks']['release_present']);
        self::assertSame('restricted', $diagnostics['checks']['sandbox_profile']);
        self::assertSame([], $diagnostics['checks']['access_policy_keys']);
        self::assertArrayHasKey('reportedAt', $diagnostics);
    }
}
