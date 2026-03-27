<?php

declare(strict_types=1);

namespace App\Component\Product\Billing;

use Doctrine\DBAL\Connection;

final class UsageTracker
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Сохранить единицу использования (напр. 1 вызов LLM) */
    public function track(string $tenantId, string $feature, int $quantity, float $unitPriceUsd, string $ts): void
    {
        $this->db->insert('billing_usage', [
            'ts' => $ts,
            'tenant_id' => $tenantId,
            'feature' => $feature,
            'quantity' => $quantity,
            'unit_price_usd' => $unitPriceUsd,
            'amount_usd' => $unitPriceUsd * $quantity,
        ]);
    }

    /** @return array<int,array<string,mixed>> */
    public function monthly(string $tenantId, string $monthIso): array
    {
        return $this->db->fetchAllAssociative(
            "SELECT feature, SUM(quantity) as qty, SUM(amount_usd) as amount FROM billing_usage 
             WHERE tenant_id=? AND to_char(ts,'YYYY-MM')=? GROUP BY feature ORDER BY amount DESC",
            [$tenantId, $monthIso]
        );
    }
}
