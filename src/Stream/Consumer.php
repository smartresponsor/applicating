<?php

declare(strict_types=1);

namespace App\Component\Product\Stream;

final class Consumer
{
    public function __construct(private readonly \Doctrine\DBAL\Connection $db)
    {
    }

    /** @return array<int,array<string,mixed>> */
    public function poll(string $topic, int $limit = 100): array
    {
        return $this->db->fetchAllAssociative(
            'SELECT * FROM stream_outbox WHERE topic=? AND status=? ORDER BY id ASC LIMIT ?',
            [$topic, 'queued', $limit]
        );
    }

    public function ack(int $id): void
    {
        $this->db->update('stream_outbox', ['status' => 'sent'], ['id' => $id]);
    }
}
