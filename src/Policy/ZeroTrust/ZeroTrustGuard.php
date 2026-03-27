<?php

declare(strict_types=1);

namespace App\Component\Product\Policy\ZeroTrust;

final class ZeroTrustGuard
{
    public function verifySignature(string $payload, string $signature, string $secret): bool
    {
        $calc = hash_hmac('sha256', $payload, $secret);

        return hash_equals($calc, $signature);
    }

    public function sanitizeHeaders(array $headers): array
    {
        unset($headers['X-Debug'], $headers['X-Bypass']);

        return $headers;
    }
}
