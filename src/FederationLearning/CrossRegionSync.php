<?php

declare(strict_types=1);

namespace App\Component\Product\FederationLearning;

use Doctrine\DBAL\Connection;

final class CrossRegionSync
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Возвращает агрегированные метрики по вариантам для отправки другим регионам. */
    public function exportPolicyStats(string $policy): array
    {
        $rows = $this->db->fetchAllAssociative("SELECT variant, COUNT(*) AS trials, SUM(CASE WHEN reward>0 THEN 1 ELSE 0 END) AS wins FROM policy_feedback WHERE policy=? AND ts>NOW()-INTERVAL '24 hours' GROUP BY variant", [$policy]);
        $stats = [];
        foreach ($rows as $r) {
            $stats[$r['variant']] = ['wins' => (int) $r['wins'], 'trials' => (int) $r['trials']];
        }

        return ['policy' => $policy, 'stats' => $stats, 'window_h' => 24];
    }
}
