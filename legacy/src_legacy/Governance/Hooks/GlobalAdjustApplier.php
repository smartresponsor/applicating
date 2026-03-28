<?php

declare(strict_types=1);

namespace App\Component\Product\Governance\Hooks;

use Doctrine\DBAL\Connection;

final class GlobalAdjustApplier
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Применяет последний global_adjust к политике скидок/лимитов (демо). */
    public function applyToTenant(string $tenantId): float
    {
        $val = (float) ($this->db->fetchOne('SELECT global_adjust FROM neural_fabric_updates ORDER BY ts DESC LIMIT 1') ?? 1.0);
        if ($val > 1.1) {
            $this->db->insert('gov_policies', [
                'scope' => $tenantId, 'name' => 'global_adjust_discount', 'rules' => json_encode(['discount_pct' => 5]), 'version' => 1, 'created_at' => gmdate('c'),
            ]);
        } elseif ($val < 0.9) {
            $this->db->insert('gov_policies', [
                'scope' => $tenantId, 'name' => 'global_adjust_limitboost', 'rules' => json_encode(['qps_bonus' => 10]), 'version' => 1, 'created_at' => gmdate('c'),
            ]);
        }

        return $val;
    }
}
