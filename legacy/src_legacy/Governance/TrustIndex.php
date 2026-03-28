<?php

declare(strict_types=1);

namespace App\Component\Product\Governance;

use Doctrine\DBAL\Connection;

final class TrustIndex
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function ratePlugin(int $pluginId, int $score, string $byUser): void
    {
        $this->db->insert('gov_trust', [
            'ts' => gmdate('c'), 'plugin_id' => $pluginId, 'score' => $score, 'by_user' => $byUser,
        ]);
    }

    public function getScore(int $pluginId): float
    {
        $val = $this->db->fetchOne('SELECT AVG(score) FROM gov_trust WHERE plugin_id=?', [$pluginId]);

        return (float) ($val ?? 0.0);
    }
}
