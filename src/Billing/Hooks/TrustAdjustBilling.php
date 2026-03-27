<?php

declare(strict_types=1);

namespace App\Component\Product\Billing\Hooks;

use Doctrine\DBAL\Connection;

final class TrustAdjustBilling
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Чем выше trust, тем ниже комиссия: price * (1 - 0.1*trust). */
    public function apply(float $base): float
    {
        $val = (float) ($this->db->fetchOne('SELECT score FROM trustmesh_scores ORDER BY ts DESC LIMIT 1') ?? 0.0);

        return round($base * (1.0 - 0.1 * $val), 6);
    }
}
