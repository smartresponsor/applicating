<?php

declare(strict_types=1);

namespace App\Component\Product\Sentient;

use Doctrine\DBAL\Connection;

final class FeedbackIngestor
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Пишет обратную связь по решению (governance/orchestrator outcome). */
    public function ingest(string $policy, string $region, float $slaOk, float $latencyMs, float $risk, float $trust): void
    {
        $this->db->insert('sentient_feedback', [
            'ts' => gmdate('c'),
            'policy' => $policy,
            'region' => $region,
            'sla_ok' => $slaOk,
            'latency_ms' => $latencyMs,
            'risk' => $risk,
            'trust' => $trust,
        ]);
    }
}
