<?php

declare(strict_types=1);

namespace App\Component\Product\Orchestration\Integration;

use Doctrine\DBAL\Connection;

final class GuardBridge
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** @param array<string,mixed> $params @param array<string,mixed> $details */
    public function maybeEnqueue(string $tenantId, string $action, array $params, string $risk, array $details = []): ?int
    {
        if ('low' === $risk) {
            return null;
        }
        $this->db->insert('approval_requests', [
            'tenant_id' => $tenantId,
            'action' => $action,
            'params' => json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'risk_level' => $risk,
            'status' => 'pending',
            'requested_by' => 'orchestrator',
            'approved_by' => null,
            'ts_request' => gmdate('c'),
            'ts_approve' => null,
            'details' => json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        return (int) $this->db->lastInsertId();
    }
}
