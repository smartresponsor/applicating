<?php

declare(strict_types=1);

namespace App\Component\Product\PolicyMesh\Agent;

use Doctrine\DBAL\Connection;

final class RegionAgent
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function status(): array
    {
        $queue = (int) ($this->db->fetchOne("SELECT COUNT(*) FROM policy_replica_queue WHERE status IN ('queued','sending')") ?? 0);
        $score = (float) ($this->db->fetchOne("SELECT AVG(score) FROM policy_simulation_log WHERE ts>NOW()-INTERVAL '1 hour'") ?? 0.0);
        $fails = (int) ($this->db->fetchOne("SELECT COUNT(*) FROM policy_replica_events WHERE ts>NOW()-INTERVAL '15 minutes' AND status='fail'") ?? 0);

        return ['queue' => $queue, 'predictive_score' => $score, 'replication_failures_15m' => $fails, 'ts' => gmdate('c')];
    }
}
