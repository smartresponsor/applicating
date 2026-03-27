<?php

declare(strict_types=1);

namespace App\Component\Product\RiskOrchestrator;

use Doctrine\DBAL\Connection;

final class PolicyOrchestrator
{
    public function __construct(
        private readonly Connection $db,
        private readonly RiskEngine $risk,
        private readonly TrustEvaluator $trust,
    ) {
    }

    /** Принимает решение: apply | hold | reject */
    public function decide(string $policy, string $region, float $impactScore = 0.5, array $thresholds = ['auto' => 0.4, 'manual' => 0.7]): array
    {
        $riskScore = $this->risk->evaluate($policy);
        $trustScore = $this->trust->evaluateRegion($region);
        $final = max(0.0, min(1.0, $riskScore * (1.0 - 0.3 * $trustScore) * (0.8 + 0.4 * $impactScore)));

        $decision = 'apply';
        if ($final >= $thresholds['manual']) {
            $decision = 'reject';
        } elseif ($final >= $thresholds['auto']) {
            $decision = 'hold';
        }

        // лог
        $this->db->insert('policy_orchestrator_decisions', [
            'ts' => gmdate('c'),
            'policy' => $policy,
            'region' => $region,
            'risk_score' => $riskScore,
            'trust_score' => $trustScore,
            'impact_score' => $impactScore,
            'final_score' => $final,
            'decision' => $decision,
        ]);

        return [
            'policy' => $policy,
            'region' => $region,
            'risk' => $riskScore,
            'trust' => $trustScore,
            'impact' => $impactScore,
            'final' => $final,
            'decision' => $decision,
        ];
    }
}
