<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Application;
use App\Entity\ApplicationManifest;
use PHPUnit\Framework\TestCase;

final class ApplicationManifestBehaviorTest extends TestCase
{
    public function testConstructorKeepsManifestMetadata(): void
    {
        $application = new Application(
            'Manifest Demo Application',
            'manifest-demo-application',
            'applicating/manifest-demo-application',
            'Applicating Labs',
            'Manifest listing summary'
        );

        $manifest = new ApplicationManifest(
            $application,
            '1.0.0',
            'io.applicating.manifest.demo',
            ['catalog', 'reporting'],
            ['tenant:read'],
            ['bootstrap'],
            'default',
            'approved',
            ['identifier' => 'io.applicating.manifest.demo']
        );

        self::assertSame($application, $manifest->getApplication());
        self::assertSame('1.0.0', $manifest->getManifestVersion());
        self::assertSame('io.applicating.manifest.demo', $manifest->getIdentifier());
        self::assertSame(['catalog', 'reporting'], $manifest->getCapabilities());
        self::assertSame(['tenant:read'], $manifest->getPermissions());
        self::assertSame(['bootstrap'], $manifest->getRuntimeHooks());
        self::assertSame('default', $manifest->getSandboxProfile());
        self::assertSame('approved', $manifest->getGovernanceState());
        self::assertSame(['identifier' => 'io.applicating.manifest.demo'], $manifest->getRawManifest());
        self::assertNotNull($manifest->getCreatedAt());
    }
}
