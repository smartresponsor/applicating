<?php

declare(strict_types=1);

namespace App\Component\Product\Billing\Payment;

final class PayPalClient
{
    public function createPayment(string $tenantId, float $amountUsd): array
    {
        return ['ok' => true, 'provider' => 'paypal', 'approval_url' => 'https://paypal.local/pay/'.$tenantId, 'amount' => $amountUsd];
    }
}
