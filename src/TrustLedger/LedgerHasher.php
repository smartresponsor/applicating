<?php

declare(strict_types=1);

namespace App\Component\Product\TrustLedger;

final class LedgerHasher
{
    public static function hash(string $prevHash, string $payload, string $signature): string
    {
        return hash('sha256', $prevHash.'|'.$payload.'|'.$signature);
    }
}
