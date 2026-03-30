<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Application;
use App\Entity\ApplicationManifest;
use App\Entity\ApplicationRelease;
use App\Entity\TenantApplication;
use App\Enum\ApplicationAccessLevel;
use PHPUnit\Framework\TestCase;

final class ApplicationEntityBehaviorTest extends TestCase
{
    public function testPublicationStateTransitionsAndMutableFields(): void
    {
        $application = $this->createApplication();

        self::assertSame('draft', $application->getPublicationState()->value);

        $application->markForModeration();
        self::assertSame('moderation', $application->getPublicationState()->value);

        $application->publish();
        self::assertSame('published', $application->getPublicationState()->value);

        $application->suspend();
        self::assertSame('suspended', $application->getPublicationState()->value);

        $application->rename('Updated Application');
        $application->changeSlug('updated-application');
        $application->changePackageName('applicating/updated-application');
        $application->changeDeveloperName('Updated Labs');
        $application->changeListingSummary('Updated summary');
        $application->changeAccessLevel(ApplicationAccessLevel::Private);
        $application->changeBillingCode('APP-UPDATED');
        $application->changeSandboxProfile('restricted');
        $application->changeEnabledByDefault(true);

        self::assertSame('Updated Application', $application->getName());
        self::assertSame('updated-application', $application->getSlug());
        self::assertSame('applicating/updated-application', $application->getPackageName());
        self::assertSame('Updated Labs', $application->getDeveloperName());
        self::assertSame('Updated summary', $application->getListingSummary());
        self::assertSame('private', $application->getAccessLevel()->value);
        self::assertSame('APP-UPDATED', $application->getBillingCode());
        self::assertSame('restricted', $application->getSandboxProfile());
        self::assertTrue($application->isEnabledByDefault());
    }

    public function testRelatedCollectionsDoNotDuplicateEntries(): void
    {
        $application = $this->createApplication();

        $release = new ApplicationRelease(
            $application,
            '1.0.0',
            'stable',
            hash('sha256', 'entity-release'),
            'https://downloads.example.test/entity-release/1.0.0.zip',
            'Entity release notes'
        );
        $manifest = new ApplicationManifest(
            $application,
            '1.0.0',
            'io.applicating.entity.demo',
            ['catalog'],
            ['tenant:read'],
            ['bootstrap'],
            'default',
            'approved',
            ['identifier' => 'io.applicating.entity.demo']
        );
        $tenantApplication = new TenantApplication(
            $application,
            'tenant-entity',
            '1.0.0',
            true,
            true,
            ['scope' => 'tenant']
        );

        $application->addRelease($release);
        $application->addRelease($release);
        $application->addManifest($manifest);
        $application->addManifest($manifest);
        $application->addTenantApplication($tenantApplication);
        $application->addTenantApplication($tenantApplication);

        self::assertCount(1, $application->getReleases());
        self::assertCount(1, $application->getManifests());
        self::assertCount(1, $application->getTenantApplications());
    }

    private function createApplication(): Application
    {
        return new Application(
            'Demo Application',
            'demo-application',
            'applicating/demo-application',
            'Applicating Labs',
            'Demo listing summary'
        );
    }
}
