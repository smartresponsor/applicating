<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\DTO\Application\ApplicationManifestData;
use App\Service\ApplicationManifestService;
use PHPUnit\Framework\TestCase;

final class ApplicationManifestServiceTest extends TestCase
{
    public function testNormalizeManifestPayloadSplitsAndTrimsLines(): void
    {
        $data = new ApplicationManifestData();
        $data->manifestVersion = '1.2.3';
        $data->identifier = 'io.applicating.manifest.demo';
        $data->capabilities = " catalog \n reporting\n\nanalytics ";
        $data->permissions = "tenant:read\n tenant:write ";
        $data->runtimeHooks = " bootstrap \nshutdown ";
        $data->sandboxProfile = 'restricted';
        $data->governanceState = 'approved';

        $service = new ApplicationManifestService();
        $payload = $service->normalizeManifestPayload($data);

        self::assertSame('1.2.3', $payload['manifestVersion']);
        self::assertSame('io.applicating.manifest.demo', $payload['identifier']);
        self::assertSame(['catalog', 'reporting', 'analytics'], $payload['capabilities']);
        self::assertSame(['tenant:read', 'tenant:write'], $payload['permissions']);
        self::assertSame(['bootstrap', 'shutdown'], $payload['runtimeHooks']);
        self::assertSame('restricted', $payload['sandboxProfile']);
        self::assertSame('approved', $payload['governanceState']);
    }
}
