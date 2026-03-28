<?php

declare(strict_types=1);

namespace App\Component\Product\ETL\Connector;

final class CRMWebhookConnector
{
    /** В реальности сюда приходит HTTP POST. Здесь — метод для записи события. */
    public function normalize(array $payload): array
    {
        return [
            'tenant_id' => (string) ($payload['tenant_id'] ?? 'tenantA'),
            'customer_id' => (string) ($payload['customer_id'] ?? 'unknown'),
            'event' => (string) ($payload['event'] ?? 'unknown'),
            'ts' => (string) ($payload['ts'] ?? gmdate('c')),
            'meta' => $payload['meta'] ?? [],
        ];
    }
}
