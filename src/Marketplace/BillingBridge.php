<?php

declare(strict_types=1);

namespace App\Component\Product\Marketplace;

use Doctrine\DBAL\Connection;

final class BillingBridge
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function subscribe(string $tenantId, int $pluginId, float $priceUsd): void
    {
        // Имитация: charge + событие подписки
        $this->db->insert('billing_events', [
            'ts' => gmdate('c'), 'tenant_id' => $tenantId, 'type' => 'charge', 'meta' => json_encode(['reason' => 'plugin_subscribe', 'plugin_id' => $pluginId, 'amount' => $priceUsd]),
        ]);
        $this->db->insert('plugin_events', [
            'ts' => gmdate('c'), 'tenant_id' => $tenantId, 'plugin_id' => $pluginId, 'event' => 'subscribed', 'payload' => json_encode(['price_usd' => $priceUsd]),
        ]);
    }
}
