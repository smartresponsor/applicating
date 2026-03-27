<?php

declare(strict_types=1);

namespace App\Component\Product\Governance;

use Doctrine\DBAL\Connection;

final class GovernanceEngine
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Сводит сигналы Orchestrator (final score/decision) и Mesh (кворум) в мета-решение. */
    public function aggregateDecision(string $policy, string $region): array
    {
        $last = $this->db->fetchAssociative('
            SELECT decision, final_score 
            FROM policy_orchestrator_decisions 
            WHERE policy=? AND region=? 
            ORDER BY ts DESC LIMIT 1
        ', [$policy, $region]) ?: ['decision' => 'apply', 'final_score' => 0.3];

        $votes = (int) ($this->db->fetchOne("
            SELECT COUNT(*) FROM policy_mesh_votes v
            JOIN policy_mesh_proposals p ON p.id=v.proposal_id
            WHERE p.topic='policy.params' AND p.payload->>'policy'= ? AND v.vote='approve' 
            AND p.ts>NOW()-INTERVAL '24 hours'
        ", [$policy]) ?? 0);

        $metaScore = max(0.0, min(1.0, (float) $last['final_score'] * (1.0 - min(0.3, $votes / 10.0))));
        $metaDecision = $last['decision'];
        if ($votes >= 3 and $metaScore < 0.6 and 'reject' != $metaDecision) {
            $metaDecision = 'apply'; // кворум поддерживает
        }

        return ['metaScore' => round($metaScore, 4), 'metaDecision' => $metaDecision, 'votes' => $votes, 'orchestrator' => $last];
    }
}
