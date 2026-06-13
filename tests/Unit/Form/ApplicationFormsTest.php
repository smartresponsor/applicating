<?php

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\Form;

use App\Applicating\Form\Application\ApplicationManifestType;
use App\Applicating\Form\Application\ApplicationReleaseType;
use App\Applicating\Form\Application\ApplicationType;
use App\Applicating\Form\Application\TenantApplicationAssignmentType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Forms;

final class ApplicationFormsTest extends TestCase
{
    #[Test]
    public function applicationFormsExposeMarketplaceFields(): void
    {
        $factory = Forms::createFormFactory();

        self::assertSame(['nameEntity', 'slug', 'packageName', 'developerName', 'listingSummary', 'accessLevel', 'billingCode', 'sandboxProfile', 'enabledByDefault'], array_keys($factory->create(ApplicationType::class)->all()));
        self::assertSame(['version', 'channel', 'checksum', 'downloadUrl', 'releaseNotes'], array_keys($factory->create(ApplicationReleaseType::class)->all()));
        self::assertSame(['manifestVersion', 'identifier', 'capabilities', 'permissions', 'runtimeHooks', 'sandboxProfile', 'governanceState'], array_keys($factory->create(ApplicationManifestType::class)->all()));
        self::assertSame(['tenantKey', 'installedVersion', 'enabled', 'billingActive', 'accessPolicy'], array_keys($factory->create(TenantApplicationAssignmentType::class)->all()));
    }
}
