<?php

declare(strict_types=1);

namespace App\Component\Product\LLM;

use Doctrine\DBAL\Connection;

final class DecisionLogger
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** @param array<string,mixed> $d */
    public function log(string $tenantId, string $advisor, string $prompt, array $d, string $source = 'llm'): void
    {
        $this->db->insert('assistant_decisions', [
            'tenant_id' => $tenantId,
            'advisor' => $advisor,
            'prompt' => $prompt,
            'decision' => json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'score' => (float) ($d['score'] ?? 0.0),
            'ts' => gmdate('c'),
            'source' => $source,
            'applied' => false,
        ]);
    }
}
