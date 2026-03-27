<?php
declare(strict_types=1);
namespace App\Billing\Product;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class StripeGateway implements BillingGatewayInterface
{
    private string $apiKey;

    public function __construct(private HttpClientInterface $http, string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /** Send usage to Stripe metered subscription item */
    public function sendUsage(string $subscriptionItemId, float $units, ?string $traceId = null): array
    {
        $url = 'https://api.stripe.com/v1/subscription_items/' . rawurlencode($subscriptionItemId) . '/usage_records';
        $body = [
            'quantity' => (int)round($units),
            'action'   => 'increment',
        ];
        if ($traceId) { $body['idempotency_key'] = $traceId; }

        $resp = $this->http->request('POST', $url, [
            'auth_basic' => ($this->apiKey . ':', ''),
            'headers' => [
                'Stripe-Version' => '2022-11-15',
                'Idempotency-Key' => $traceId ?? bin2hex(random_bytes(8)),
            ],
            'body' => $body,
            'timeout' => 15
        ]);

        if (201 != $resp->getStatusCode()) {
            throw new \RuntimeException('Stripe usage error: ' . $resp->getStatusCode() . ' ' . $resp->getContent(false));
        }
        return $resp->toArray(false);
    }

    /** Create and finalize an invoice for the customer mapped by tenantId (expects external mapping in your app) */
    public function settleInvoice(string $tenantId, string $currency = 'USD'): array
    {
        // Your side: resolve/customer by tenantId
        // For safety, we expect env var STRIPE_CUSTOMER_MAP_{TENANTID}
        $envKey = 'STRIPE_CUSTOMER_MAP_' . strtoupper(preg_replace('/[^a-z0-9]/i', '_', $tenantId));
        $customerId = getenv($envKey);
        if (!$customerId) {
            throw new \RuntimeException('Stripe customer mapping not found for tenantId=' . $tenantId . ' (env ' . $envKey . ')');
        }

        // Create invoice
        $create = $this->http->request('POST', 'https://api.stripe.com/v1/invoices', [
            'auth_basic' => ($this->apiKey . ':', ''),
            'body' => [ 'customer' => $customerId, 'currency' => $currency ],
            'timeout' => 15
        ]);
        if (200 != $create->getStatusCode()) { throw new \RuntimeException('Stripe invoice create failed: '.$create->getContent(false)); }
        $invoice = $create->toArray(false);

        // Finalize invoice
        $finalize = $this->http->request('POST', 'https://api.stripe.com/v1/invoices/' . $invoice['id'] . '/finalize', [
            'auth_basic' => ($this->apiKey . ':', ''),
            'timeout' => 15
        ]);
        if (200 != $finalize->getStatusCode()) { throw new \RuntimeException('Stripe invoice finalize failed: '.$finalize->getContent(false)); }

        return $finalize->toArray(false);
    }
}
