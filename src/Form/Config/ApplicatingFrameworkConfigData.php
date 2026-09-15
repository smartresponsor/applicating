<?php

declare(strict_types=1);

namespace App\Applicating\Form\Config;

final class ApplicatingFrameworkConfigData
{
    public string $csrfProtectionEnabled = '1';
    public string $formEnabled = '1';
    public string $validationEnabled = '1';
    public string $sessionCookieSecure = 'auto';
    public string $sessionCookieSameSite = 'lax';
    public string $loginThrottleLimit = '5';
    public string $loginThrottleIntervalMinutes = '15';
    public string $adminApiThrottleLimit = '60';
    public string $adminApiThrottleAmount = '60';
    public string $adminApiThrottleIntervalMinutes = '1';
}
