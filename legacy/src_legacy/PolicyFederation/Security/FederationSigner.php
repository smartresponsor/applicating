<?php

declare(strict_types=1);

namespace App\Component\Product\PolicyFederation\Security;

final class FederationSigner
{
    public function __construct(private readonly string $secret = 'federation-secret')
    {
    }

    public function sign(string $payload): string
    {
        return hash_hmac('sha256', $payload, $this->secret);
    }

    public function verify(string $payload, string $sig): bool
    {
        return hash_equals($this->sign($payload), $sig);
    }
}
