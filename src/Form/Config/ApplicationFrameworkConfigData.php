<?php

declare(strict_types=1);

namespace App\Applicating\Form\Config;

/**
 * Carries editable framework, session, and throttling settings for the Applicating configuration form.
 */
final class ApplicationFrameworkConfigData
{
    public bool $csrfProtectionEnabled = true;
    public bool $formEnabled = true;
    public bool $validationEnabled = true;
    public string $sessionCookieSecure = 'auto';
    public string $sessionCookieSameSite = 'lax';
    public string $loginThrottleLimit = '5';
    public string $loginThrottleIntervalMinutes = '15';
    public string $adminApiThrottleLimit = '60';
    public string $adminApiThrottleAmount = '60';
    public string $adminApiThrottleIntervalMinutes = '1';
}
