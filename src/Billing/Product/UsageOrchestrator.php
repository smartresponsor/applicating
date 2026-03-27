<?php

declare(strict_types=1);

namespace App\Billing\Product;

use Doctrine\DBAL\Connection;
use Symfony\Component\Uid\Uuid;

final class UsageOrchestrator
{
    public function __construct(
        private Connection $db,
        private BillingGatewayInterface $gateway,
    ) {
    }

    public function trackUsage(string $tenantId, int $productId, float $units, string $currency = 'USD', ?string $traceId = null): array
    {
        $traceId = $traceId ?: Uuid::v4()->toRfc4122();

        // persist usage locally
        $this->db->insert('billing_usage_log', [
            'tenant_id' => $tenantId,
            'product_id' => $productId,
            'units' => $units,
            'currency' => $currency,
            'trace_id' => $traceId,
        ]);

        // resolve subscription item id
        $map = $this->db->fetchAssociative('SELECT subscription_item_id FROM billing_stripe_map WHERE tenant_id = ? AND product_id = ?', [$tenantId, $productId]);
        if (!$map) {
            throw new \RuntimeException('Stripe map not found for tenant='.$tenantId.' product='.$productId);
        }

        // push to Stripe
        $providerPayload = $this->gateway->sendUsage($map['subscription_item_id'], $units, $traceId);

        // mark sent
        $this->db->executeStatement(
            'UPDATE billing_usage_log SET sent_to_provider = TRUE, provider_response = provider_response || ?::jsonb WHERE trace_id = ?',
            [json_encode(['stripe' => $providerPayload]), $traceId]
        );

        return ['ok' => true, 'traceId' => $traceId, 'provider' => 'stripe', 'payload' => $providerPayload];
    }

    public function settleInvoice(string $tenantId, string $currency = 'USD'): array
    {
        $payload = $this->gateway->settleInvoice($tenantId, $currency);
        // store invoice entry
        $this->db->insert('billing_invoice', [
            'tenant_id' => $tenantId,
            'currency' => $currency,
            'provider' => 'stripe',
            'external_id' => $payload['id'] ?? null,
            'status' => $payload['status'] ?? 'open',
            'total' => (float) ($payload['amount_due'] ?? 0) / 100.0,
        ]);

        return ['ok' => true, 'provider' => 'stripe', 'invoice' => $payload];
    }
}
