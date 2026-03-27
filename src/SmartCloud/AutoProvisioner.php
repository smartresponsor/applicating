<?php

declare(strict_types=1);

namespace App\Component\Product\SmartCloud;

use Doctrine\DBAL\Connection;

final class AutoProvisioner
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Имитация: создание стандартных записей для нового tenant (Billing, Marketplace, Governance). */
    public function bootstrap(string $tenantId): void
    {
        $this->db->insert('tenant_events', [
            'ts' => gmdate('c'), 'tenant_id' => $tenantId, 'type' => 'bootstrap', 'payload' => '{}', 'status' => 'done',
        ]);
        // прайс по умолчанию
        $this->db->executeStatement("INSERT INTO billing_prices(feature, unit_price_usd) VALUES('llm.call', 0.002) ON CONFLICT (feature) DO NOTHING");
    }
}
