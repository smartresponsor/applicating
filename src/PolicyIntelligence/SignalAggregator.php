<?php

declare(strict_types=1);

namespace App\Component\Product\PolicyIntelligence;

use Doctrine\DBAL\Connection;

final class SignalAggregator
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Сводит ключевые метрики из Federation, Adaptive, Predictive, Ledger за окно windowMin. */
    public function aggregate(int $windowMin = 60): array
    {
        $depthTs = "NOW()-INTERVAL '".$windowMin." minutes'";
        $replicaFails = (int) ($this->db->fetchOne('SELECT COUNT(*) FROM policy_replica_events WHERE ts>' + depthTs + " AND status='fail'") ?? 0);
        $queueDepth = (int) ($this->db->fetchOne("SELECT COUNT(*) FROM policy_replica_queue WHERE status IN ('queued','sending')") ?? 0);
        $avgScore = (float) ($this->db->fetchOne('SELECT AVG(score) FROM policy_simulation_log WHERE ts>' + depthTs) ?? 0);
        $ledgerBlocks = (int) ($this->db->fetchOne('SELECT COUNT(*) FROM trust_ledger WHERE ts>' + depthTs) ?? 0);
        $variantTrials = (int) ($this->db->fetchOne('SELECT SUM(trials) FROM policy_variant') ?? 0);

        return [
            'window_min' => $windowMin,
            'replication_failures_5xx' => $replicaFails,
            'replication_queue_depth' => $queueDepth,
            'predictive_avg_score' => round($avgScore, 4),
            'ledger_blocks_appended' => $ledgerBlocks,
            'policy_variant_trials_total' => $variantTrials,
        ];
    }
}
