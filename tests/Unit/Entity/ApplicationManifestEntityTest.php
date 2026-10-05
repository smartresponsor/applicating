<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\Entity;

use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Entity\ApplicationManifestEntity;
use PHPUnit\Framework\TestCase;

final class ApplicationManifestEntityTest extends TestCase
{
    public function testManifestExposesCanonicalState(): void
    {
        $application = new ApplicationEntity(
            'Demo Application',
            'demo-application',
            'demo/application',
            'Demo Developer',
            'Demo application summary.',
        );
        $rawManifest = ['name' => 'demo', 'version' => '1.0'];
        $manifest = new ApplicationManifestEntity(
            $application,
            '1.0',
            'demo.manifest',
            ['reporting', 'search'],
            ['catalog.read', 'catalog.write'],
            ['boot', 'shutdown'],
            'restricted',
            'approved',
            $rawManifest,
        );

        self::assertNull($manifest->getId());
        self::assertSame($application, $manifest->getApplication());
        self::assertSame('1.0', $manifest->getManifestVersion());
        self::assertSame('demo.manifest', $manifest->getIdentifier());
        self::assertSame(['reporting', 'search'], $manifest->getCapabilities());
        self::assertSame(['catalog.read', 'catalog.write'], $manifest->getPermissions());
        self::assertSame(['boot', 'shutdown'], $manifest->getRuntimeHooks());
        self::assertSame('restricted', $manifest->getSandboxProfile());
        self::assertSame('approved', $manifest->getGovernanceState());
        self::assertSame($rawManifest, $manifest->getRawManifest());
        self::assertInstanceOf(\DateTimeImmutable::class, $manifest->getCreatedAt());
    }
}
