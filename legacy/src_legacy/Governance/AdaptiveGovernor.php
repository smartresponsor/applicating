<?php

declare(strict_types=1);

namespace App\Component\Product\Governance;

use Doctrine\DBAL\Connection;

final class AdaptiveGovernor
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Меняет лимиты rollout на основе metaScore и текущих SLO/SLA. */
    public function enforceLimits(string $policy, float $metaScore): array
    {
        $percent = 20;
        if ($metaScore < 0.4) {
            $percent = 0;
        } elseif ($metaScore < 0.55) {
            $percent = 10;
        } elseif ($metaScore > 0.8) {
            $percent = 50;
        }

        // Записываем в таблицу настроек (или вызываем Scheduler override)
        $this->db->insert('policy_governance_actions', [
            'ts' => gmdate('c'),
            'policy' => $policy,
            'action' => 'set_canary_percent',
            'value' => $percent,
        ]);

        return ['ok' => true, 'canary_percent' => $percent];
    }
}
