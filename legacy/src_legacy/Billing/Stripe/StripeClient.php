<?php

declare(strict_types=1);

namespace App\Component\Product\Billing\Stripe;

final class StripeClient
{
    public function __construct(private readonly string $apiKey)
    {
    }

    /** @param array<string,mixed> $data */
    public function createCustomer(array $data): array
    {
        return ['id' => 'cus_demo'];
    }

    /** @param array<string,mixed> $data */
    public function createSubscription(array $data): array
    {
        return ['id' => 'sub_demo', 'status' => 'active'];
    }

    public function createInvoiceItem(string $customerId, int $amountCents, string $currency, string $desc): array
    {
        return ['id' => 'ii_demo'];
    }

    public function finalizeInvoice(string $customerId): array
    {
        return ['id' => 'inv_demo'];
    }
}
