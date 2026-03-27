<?php

declare(strict_types=1);

namespace App\Component\Product\Guard;

use Doctrine\DBAL\Connection;

final class ApprovalService
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function approve(int $id, string $who): bool
    {
        $n = $this->db->executeStatement(
            'UPDATE approval_requests SET status=?, approved_by=?, ts_approve=? WHERE id=? AND status=?',
            ['approved', $who, gmdate('c'), $id, 'pending']
        );

        return $n > 0;
    }

    public function reject(int $id, string $who, string $reason = ''): bool
    {
        $n = $this->db->executeStatement(
            'UPDATE approval_requests SET status=?, approved_by=?, ts_approve=?, details = details || :reason WHERE id=? AND status=?',
            ['rejected', $who, gmdate('c'), json_encode(['reject_reason' => $reason], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $id, 'pending']
        );

        return $n > 0;
    }

    /** Выполнить действие после approve — демо no-op, в проде: вызов orchestrator-а. */
    public function executeApproved(int $id): array
    {
        $row = $this->db->fetchAssociative('SELECT * FROM approval_requests WHERE id=?', [$id]);
        if (!$row || 'approved' !== $row['status']) {
            return ['ok' => false, 'reason' => 'not approved'];
        }

        // demo: вернуть как будто действие выполнено
        return ['ok' => true, 'action' => $row['action'], 'params' => json_decode($row['params'] ?? '[]', true)];
    }
}
