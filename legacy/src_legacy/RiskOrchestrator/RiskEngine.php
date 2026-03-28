<?php

declare(strict_types=1);

namespace App\Component\Product\RiskOrchestrator;

use Doctrine\DBAL\Connection;

final class RiskEngine
{
    public function __construct(private readonly Connection $db)
    {
    }

    /**
     * Оценивает риск политики на основе метрик Intelligence Hub и истории.
     * Возвращает riskScore в диапазоне [0,1].
     */
    public function evaluate(string $policy): float
    {
        $score = (float) ($this->db->fetchOne("SELECT AVG(score) FROM policy_simulation_log WHERE policy=? AND ts>NOW()-INTERVAL '7 days'", [$policy]) ?? 0.5);
        $fails = (int) ($this->db->fetchOne("SELECT COUNT(*) FROM policy_replica_events WHERE ts>NOW()-INTERVAL '24 hours' AND status='fail'") ?? 0);
        $qdepth = (int) ($this->db->fetchOne("SELECT COUNT(*) FROM policy_replica_queue WHERE status IN ('queued','sending')") ?? 0);

        // Чем ниже predictive score — тем выше риск; чем больше фейлов/очередь — тем выше риск.
        $risk = (1.0 - max(0.0, min(1.0, $score))) * 0.6
              + min(1.0, $fails / 20.0) * 0.25
              + min(1.0, $qdepth / 100.0) * 0.15;

        return round(max(0.0, min(1.0, $risk)), 4);
    }
}
