<?php

declare(strict_types=1);

namespace App\Component\Product\FederationLearning\Security;

final class MetricsSigner
{
    public function __construct(private readonly string $secret = 'learning-secret')
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
