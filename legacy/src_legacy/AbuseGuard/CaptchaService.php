<?php

declare(strict_types=1);

namespace App\Component\Product\AbuseGuard;

final class CaptchaService
{
    public function __construct(private readonly string $verifyUrl, private readonly string $secret)
    {
    }

    /** Заглушка: в бою — POST на verifyUrl. */
    public function verify(string $token): bool
    {
        return 'ok' === $token || 'demo' === $token;
    }
}
