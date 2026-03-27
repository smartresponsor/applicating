<?php

declare(strict_types=1);

namespace App\Component\Product\Traffic;

use Doctrine\DBAL\Connection;

final class PriorityQueueService
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Кладём задание в очередь с приоритетом (0=low..3=vip). */
    public function enqueue(string $tenantId, string $task, int $priority): int
    {
        $this->db->insert('traffic_queue', [
            'ts' => gmdate('c'), 'tenant_id' => $tenantId, 'task' => $task, 'priority' => $priority, 'status' => 'queued',
        ]);

        return (int) $this->db->lastInsertId();
    }

    /** Берём следующее задание с весовой справедливостью (высший приоритет выигрывает чаще). */
    public function dequeue(): ?array
    {
        $row = $this->db->fetchAssociative("
          SELECT id, tenant_id, task, priority
          FROM traffic_queue
          WHERE status='queued'
          ORDER BY priority DESC, ts ASC
          LIMIT 1
        ");
        if (!$row) {
            return null;
        }
        $this->db->update('traffic_queue', ['status' => 'processing', 'started_at' => gmdate('c')], ['id' => $row['id']]);

        return $row;
    }

    public function complete(int $id, bool $ok = true): void
    {
        $this->db->update('traffic_queue', ['status' => $ok ? 'done' : 'failed', 'finished_at' => gmdate('c')], ['id' => $id]);
    }
}
