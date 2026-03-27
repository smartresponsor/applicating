<?php

declare(strict_types=1);

namespace App\Component\Product\PolicyLearning;

use Doctrine\DBAL\Connection;

final class FeedbackIngestor
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function record(string $policy, string $variant, string $tenant, float $reward, array $context = []): void
    {
        $this->db->insert('policy_feedback', [
            'ts' => gmdate('c'),
            'policy' => $policy,
            'variant' => $variant,
            'tenant_id' => $tenant,
            'reward' => $reward,
            'context' => json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }
}
