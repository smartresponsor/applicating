<?php

declare(strict_types=1);

namespace App\Component\Product\AI;

use Doctrine\DBAL\Connection;

final class PolicyRewriter
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Наивно обновляет политику: если был выброс — временно запретить тяжелые действия. */
    public function rewrite(string $tenantId, bool $anomaly): void
    {
        $rules = $anomaly ? ['deny' => ['plugin.install', 'llm.long_task']] : ['deny' => []];
        $this->db->insert('gov_policies', [
            'scope' => $tenantId, 'name' => 'ai_autopolicy',
            'rules' => json_encode($rules, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'version' => 1, 'created_at' => gmdate('c'),
        ]);
    }
}
