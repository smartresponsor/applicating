<?php

declare(strict_types=1);

namespace App\Component\Product\TrustMesh\Security;

/**
 * Заглушка под Ed25519: пока HMAC-SHA256, интерфейс совместим для замены в бою.
 */
final class SecureBridge
{
    public function __construct(private readonly string $secret = 'trustmesh-secret')
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
