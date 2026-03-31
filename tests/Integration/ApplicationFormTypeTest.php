<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\DTO\Application\ApplicationManifestData;
use App\DTO\Application\ApplicationReleaseData;
use App\DTO\Application\ApplicationUpsertData;
use App\DTO\Application\TenantApplicationAssignmentData;
use App\Form\Application\ApplicationManifestType;
use App\Form\Application\ApplicationReleaseType;
use App\Form\Application\ApplicationType;
use App\Form\Application\TenantApplicationAssignmentType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;

final class ApplicationFormTypeTest extends KernelTestCase
{
    private FormFactoryInterface $formFactory;

    protected function setUp(): void
    {
        self::bootKernel();
        /** @var FormFactoryInterface $formFactory */
        $formFactory = static::getContainer()->get('form.factory');
        $this->formFactory = $formFactory;
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }

    public function testApplicationTypeBuildsExpectedFieldsAndDataClass(): void
    {
        $form = $this->formFactory->create(ApplicationType::class, new ApplicationUpsertData());

        self::assertSame(ApplicationUpsertData::class, $form->getConfig()->getDataClass());
        self::assertTrue($form->has('name'));
        self::assertTrue($form->has('slug'));
        self::assertTrue($form->has('packageName'));
        self::assertTrue($form->has('developerName'));
        self::assertTrue($form->has('listingSummary'));
        self::assertTrue($form->has('accessLevel'));
        self::assertTrue($form->has('billingCode'));
        self::assertTrue($form->has('sandboxProfile'));
        self::assertTrue($form->has('enabledByDefault'));
    }

    public function testApplicationReleaseTypeBuildsExpectedFieldsAndDataClass(): void
    {
        $form = $this->formFactory->create(ApplicationReleaseType::class, new ApplicationReleaseData());

        self::assertSame(ApplicationReleaseData::class, $form->getConfig()->getDataClass());
        self::assertTrue($form->has('version'));
        self::assertTrue($form->has('channel'));
        self::assertTrue($form->has('checksum'));
        self::assertTrue($form->has('downloadUrl'));
        self::assertTrue($form->has('releaseNotes'));
    }

    public function testApplicationManifestTypeBuildsExpectedFieldsAndDataClass(): void
    {
        $form = $this->formFactory->create(ApplicationManifestType::class, new ApplicationManifestData());

        self::assertSame(ApplicationManifestData::class, $form->getConfig()->getDataClass());
        self::assertTrue($form->has('manifestVersion'));
        self::assertTrue($form->has('identifier'));
        self::assertTrue($form->has('capabilities'));
        self::assertTrue($form->has('permissions'));
        self::assertTrue($form->has('runtimeHooks'));
        self::assertTrue($form->has('sandboxProfile'));
        self::assertTrue($form->has('governanceState'));
    }

    public function testTenantApplicationAssignmentTypeBuildsExpectedFieldsAndDataClass(): void
    {
        $form = $this->formFactory->create(TenantApplicationAssignmentType::class, new TenantApplicationAssignmentData());

        self::assertSame(TenantApplicationAssignmentData::class, $form->getConfig()->getDataClass());
        self::assertTrue($form->has('tenantKey'));
        self::assertTrue($form->has('installedVersion'));
        self::assertTrue($form->has('enabled'));
        self::assertTrue($form->has('billingActive'));
        self::assertTrue($form->has('accessPolicy'));
    }
}
