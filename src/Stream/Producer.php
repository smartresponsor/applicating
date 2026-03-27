<?php

declare(strict_types=1);

namespace App\Component\Product\Stream;

use App\Component\Product\Stream\DTO\EventMessage;

final class Producer
{
    /** Демонстрация: запись в таблицу stream_outbox как имитация отправки в Kafka. */
    public function __construct(private readonly \Doctrine\DBAL\Connection $db)
    {
    }

    public function send(string $topic, EventMessage $m): void
    {
        $this->db->insert('stream_outbox', [
            'ts' => $m->ts,
            'topic' => $topic,
            'tenant_id' => $m->tenantId,
            'type' => $m->type,
            'payload' => json_encode($m->payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'status' => 'queued',
        ]);
    }
}
