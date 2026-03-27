<?php

declare(strict_types=1);

namespace App\Component\Product\Billing\Payment;

final class StripeClient
{
    public function createCheckout(string $tenantId, float $amountUsd): array
    {
        return ['ok' => true, 'provider' => 'stripe', 'checkout_url' => 'https://stripe.local/checkout/'.$tenantId, 'amount' => $amountUsd];
    }
}
