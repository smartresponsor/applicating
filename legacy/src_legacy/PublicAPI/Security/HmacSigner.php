<?php

declare(strict_types=1);

namespace App\Component\Product\PublicAPI\Security;

final class HmacSigner
{
    public static function sign(string $secret, string $payload): string
    {
        return 'sha256='.hash_hmac('sha256', $payload, $secret);
    }

    public static function verify(string $secret, string $payload, string $signature): bool
    {
        $calc = self::sign($secret, $payload);

        return hash_equals($calc, $signature);
    }
}
