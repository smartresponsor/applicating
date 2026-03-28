<?php

declare(strict_types=1);

namespace App\Component\Product\PolicyFederation;

use Doctrine\DBAL\Connection;

final class PolicyReplicator
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function markForReplication(string $name, int $version): void
    {
        $this->db->insert('policy_replica_queue', [
            'ts' => gmdate('c'), 'name' => $name, 'version' => $version, 'status' => 'queued',
        ]);
    }

    public function nextTask(): ?array
    {
        $row = $this->db->fetchAssociative("SELECT * FROM policy_replica_queue WHERE status='queued' ORDER BY ts ASC LIMIT 1");
        if (!$row) {
            return null;
        }
        $this->db->update('policy_replica_queue', ['status' => 'sending', 'started_at' => gmdate('c')], ['id' => $row['id']]);

        return $row;
    }

    public function complete(int $id, bool $ok = true, ?string $err = null): void
    {
        $this->db->update('policy_replica_queue', ['status' => $ok ? 'done' : 'failed', 'finished_at' => gmdate('c'), 'error' => $err], ['id' => $id]);
    }
}
