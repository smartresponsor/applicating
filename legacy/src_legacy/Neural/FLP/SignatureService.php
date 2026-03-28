<?php

declare(strict_types=1);

namespace App\Component\Product\Neural\FLP;

final class SignatureService
{
    private string $secret;

    public function __construct(string $secret = 'flp-demo-secret')
    {
        $this->secret = $secret;
    }

    public function sign(string $payload): string
    {
        return hash_hmac('sha256', $payload, $this->secret);
    }

    public function verify(string $payload, string $signature): bool
    {
        $calc = $this->sign($payload);

        return hash_equals($calc, $signature);
    }
}
