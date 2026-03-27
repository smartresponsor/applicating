<?php

declare(strict_types=1);

namespace App\Component\Product\ETL\Connector;

final class StripeConnector
{
    /**
     * Демонстрационный заглушечный коннектор: в проде — использовать stripe-php SDK.
     *
     * @return array<int,array<string,mixed>>
     */
    public function fetchInvoices(string $tenantId, string $fromDate): array
    {
        // demo: вернём статический пример
        return [[
            'tenant_id' => $tenantId,
            'invoice_id' => 'in_demo_001',
            'amount_cents' => 12345,
            'currency' => 'usd',
            'period_start' => $fromDate,
            'period_end' => date('Y-m-d'),
            'status' => 'paid',
        ]];
    }
}
