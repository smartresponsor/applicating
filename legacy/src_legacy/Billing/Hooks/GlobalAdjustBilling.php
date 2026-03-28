<?php

declare(strict_types=1);

namespace App\Component\Product\Billing\Hooks;

use Doctrine\DBAL\Connection;

final class GlobalAdjustBilling
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Корректирует цену unit_price_usd на основе global_adjust (демо). */
    public function adjustedUnitPrice(float $base): float
    {
        $val = (float) ($this->db->fetchOne('SELECT global_adjust FROM neural_fabric_updates ORDER BY ts DESC LIMIT 1') ?? 1.0);

        return round($base * (2.0 - $val), 6); // если adjust=1.2 → 0.8x цены
    }
}
