<?php

declare(strict_types=1);

namespace App\Component\Product\Marketplace;

final class SignatureVerifier
{
    /** Наивная проверка подписи: хэш manifest+secret */
    public function verify(string $manifestJson, string $signature, string $publicKey = 'demo'): bool
    {
        return hash('sha256', $manifestJson.'#'.$publicKey) === $signature;
    }
}
