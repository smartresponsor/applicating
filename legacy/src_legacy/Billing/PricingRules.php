<?php

declare(strict_types=1);

namespace App\Component\Product\Billing;

use Doctrine\DBAL\Connection;

final class PricingRules
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Вернуть цену за единицу для фичи (с учётом времени суток/скидок — упрощённо). */
    public function unitPrice(string $feature): float
    {
        $row = $this->db->fetchAssociative('SELECT unit_price_usd FROM billing_prices WHERE feature=?', [$feature]);

        return (float) ($row['unit_price_usd'] ?? 0.01);
    }
}
