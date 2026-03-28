<?php

declare(strict_types=1);

namespace App\Component\Product\RiskOrchestrator;

use Doctrine\DBAL\Connection;

final class TrustEvaluator
{
    public function __construct(private readonly Connection $db)
    {
    }

    /**
     * Оценивает доверие к региону по данным Ledger/аудита.
     * Возвращает trustScore [0,1].
     */
    public function evaluateRegion(string $region): float
    {
        $ok = (int) ($this->db->fetchOne("SELECT COUNT(*) FROM trust_ledger WHERE ts>NOW()-INTERVAL '7 days'") ?? 0);
        $bad = (int) ($this->db->fetchOne("SELECT COUNT(*) FROM policy_replica_events WHERE ts>NOW()-INTERVAL '7 days' AND status='fail'") ?? 0);
        $score = max(0.0, min(1.0, 0.7 + min(0.3, $ok / 100.0) - min(0.4, $bad / 50.0)));

        return round($score, 4);
    }
}
