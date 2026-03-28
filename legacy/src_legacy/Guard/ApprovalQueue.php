<?php

declare(strict_types=1);

namespace App\Component\Product\Guard;

use Doctrine\DBAL\Connection;

final class ApprovalQueue
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** @param array<string,mixed> $params @param array<string,mixed> $details */
    public function enqueue(string $tenantId, string $action, array $params, string $risk, string $requestedBy, array $details = []): int
    {
        $this->db->insert('approval_requests', [
            'tenant_id' => $tenantId,
            'action' => $action,
            'params' => json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'risk_level' => $risk,
            'status' => 'pending',
            'requested_by' => $requestedBy,
            'approved_by' => null,
            'ts_request' => gmdate('c'),
            'ts_approve' => null,
            'details' => json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        return (int) $this->db->lastInsertId();
    }

    /** @return array<int,array<string,mixed>> */
    public function listPending(string $tenantId): array
    {
        return $this->db->fetchAllAssociative('SELECT * FROM approval_requests WHERE tenant_id=? AND status=? ORDER BY ts_request ASC', [$tenantId, 'pending']);
    }
}
