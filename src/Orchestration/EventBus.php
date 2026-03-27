<?php

declare(strict_types=1);

namespace App\Component\Product\Orchestration;

use App\Component\Product\Orchestration\DTO\EventDTO;
use Doctrine\DBAL\Connection;

final class EventBus
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function emit(EventDTO $e): int
    {
        $this->db->insert('orchestration_events', [
            'ts' => $e->ts,
            'tenant_id' => $e->tenantId,
            'type' => $e->type,
            'payload' => json_encode($e->payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'status' => 'queued',
        ]);

        return (int) $this->db->lastInsertId();
    }

    /** @return array<int,array<string,mixed>> */
    public function fetchQueued(int $limit = 100): array
    {
        return $this->db->fetchAllAssociative(
            'SELECT * FROM orchestration_events WHERE status=? ORDER BY id ASC LIMIT ?',
            ['queued', $limit]
        );
    }

    public function markProcessed(int $id, string $status = 'processed', ?string $info = null): void
    {
        $this->db->update('orchestration_events', ['status' => $status, 'info' => $info], ['id' => $id]);
    }
}
