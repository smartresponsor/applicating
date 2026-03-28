<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\DTO\Application\ApplicationManifestData;
use App\Service\ApplicationManifestService;
use PHPUnit\Framework\TestCase;

final class ApplicationManifestServiceTest extends TestCase
{
    public function testNormalizesManifestPayload(): void
    {
        $data = new ApplicationManifestData();
        $data->identifier = 'io.applicating.demo.application';
        $data->capabilities = "catalog\nreporting";
        $data->permissions = "tenant:read\ntenant:write";
        $data->runtimeHooks = "bootstrap\npost_install";

        $payload = (new ApplicationManifestService())->normalizeManifestPayload($data);

        self::assertSame(['catalog', 'reporting'], $payload['capabilities']);
        self::assertSame(['tenant:read', 'tenant:write'], $payload['permissions']);
        self::assertSame(['bootstrap', 'post_install'], $payload['runtimeHooks']);
    }
}
