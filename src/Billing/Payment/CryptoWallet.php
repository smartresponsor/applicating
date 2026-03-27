<?php

declare(strict_types=1);

namespace App\Component\Product\Billing\Payment;

final class CryptoWallet
{
    public function depositAddress(string $tenantId, string $asset = 'USDT'): array
    {
        return ['ok' => true, 'asset' => $asset, 'address' => '0xDEMO'.$tenantId];
    }
}
