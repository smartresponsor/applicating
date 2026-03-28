<?php

declare(strict_types=1);

namespace App\Billing\Product;

interface BillingGatewayInterface
{
    /** Send metered usage for a subscription item (provider-specific). Return provider payload. */
    public function sendUsage(string $subscriptionItemId, float $units, ?string $traceId = null): array;

    /** Create/finalize invoice for a tenant. Return provider invoice payload. */
    public function settleInvoice(string $tenantId, string $currency = 'USD'): array;
}
