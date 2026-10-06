<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\Form\Config;

use App\Applicating\Form\Config\ApplicationFrameworkConfigData;
use App\Applicating\Form\Config\ApplicationFrameworkConfigFormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Test\TypeTestCase;

final class ApplicationFrameworkConfigFormTypeTest extends TypeTestCase
{
    public function testFormBuildsCanonicalFrameworkConfigurationSurface(): void
    {
        $data = new ApplicationFrameworkConfigData();
        $form = $this->factory->create(ApplicationFrameworkConfigFormType::class, $data);

        self::assertSame(ApplicationFrameworkConfigData::class, $form->getConfig()->getDataClass());
        self::assertTrue($form->has('csrfProtectionEnabled'));
        self::assertTrue($form->has('formEnabled'));
        self::assertTrue($form->has('validationEnabled'));
        self::assertTrue($form->has('sessionCookieSecure'));
        self::assertTrue($form->has('sessionCookieSameSite'));
        self::assertTrue($form->has('loginThrottleLimit'));
        self::assertTrue($form->has('loginThrottleIntervalMinutes'));
        self::assertTrue($form->has('adminApiThrottleLimit'));
        self::assertTrue($form->has('adminApiThrottleAmount'));
        self::assertTrue($form->has('adminApiThrottleIntervalMinutes'));
        self::assertTrue($form->has('save'));
        self::assertTrue($form->has('apply'));

        self::assertSame('FRAMEWORK_CSRF_PROTECTION_ENABLED', $form->get('csrfProtectionEnabled')->getConfig()->getOption('label'));
        self::assertSame('FRAMEWORK_SESSION_COOKIE_SECURE', $form->get('sessionCookieSecure')->getConfig()->getOption('label'));
        self::assertSame('FRAMEWORK_SESSION_COOKIE_SAMESITE', $form->get('sessionCookieSameSite')->getConfig()->getOption('label'));
        self::assertSame('5', $form->get('loginThrottleLimit')->getConfig()->getOption('empty_data'));
        self::assertSame('15', $form->get('loginThrottleIntervalMinutes')->getConfig()->getOption('empty_data'));
        self::assertSame('60', $form->get('adminApiThrottleLimit')->getConfig()->getOption('empty_data'));
        self::assertSame('60', $form->get('adminApiThrottleAmount')->getConfig()->getOption('empty_data'));
        self::assertSame('1', $form->get('adminApiThrottleIntervalMinutes')->getConfig()->getOption('empty_data'));
        self::assertSame(SubmitType::class, get_class($form->get('save')->getConfig()->getType()->getInnerType()));
        self::assertSame('Save pending', $form->get('save')->getConfig()->getOption('label'));
        self::assertSame('Apply now', $form->get('apply')->getConfig()->getOption('label'));
        self::assertSame(['class' => 'btn btn-primary'], $form->get('apply')->getConfig()->getOption('attr'));
    }

    public function testSubmittedValuesMapBackToFrameworkConfigurationData(): void
    {
        $data = new ApplicationFrameworkConfigData();
        $form = $this->factory->create(ApplicationFrameworkConfigFormType::class, $data);
        $form->submit([
            'csrfProtectionEnabled' => '1',
            'formEnabled' => '1',
            'validationEnabled' => '1',
            'sessionCookieSecure' => 'auto',
            'sessionCookieSameSite' => 'strict',
            'loginThrottleLimit' => '7',
            'loginThrottleIntervalMinutes' => '20',
            'adminApiThrottleLimit' => '80',
            'adminApiThrottleAmount' => '80',
            'adminApiThrottleIntervalMinutes' => '2',
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertSame('strict', $data->sessionCookieSameSite);
        self::assertSame('7', $data->loginThrottleLimit);
        self::assertSame('20', $data->loginThrottleIntervalMinutes);
        self::assertSame('80', $data->adminApiThrottleLimit);
        self::assertSame('2', $data->adminApiThrottleIntervalMinutes);
    }
}
