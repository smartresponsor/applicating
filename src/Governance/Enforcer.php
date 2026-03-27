<?php

declare(strict_types=1);

namespace App\Component\Product\Governance;

use Doctrine\DBAL\Connection;

final class Enforcer
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Наивное применение: если policy запрещает действие — пишем событие и возвращаем false. */
    public function allow(string $tenantId, string $action, array $context = []): bool
    {
        $row = $this->db->fetchAssociative('SELECT rules FROM gov_policies WHERE scope=? ORDER BY created_at DESC LIMIT 1', [$tenantId]);
        if (!$row) {
            return true;
        }
        $rules = json_decode((string) $row['rules'], true) ?: [];
        $deny = $rules['deny'] ?? [];
        if (in_array($action, $deny, true)) {
            $this->db->insert('gov_enforcements', [
                'ts' => gmdate('c'), 'tenant_id' => $tenantId, 'action' => $action, 'context' => json_encode($context), 'result' => 'denied',
            ]);

            return false;
        }

        return true;
    }
}
