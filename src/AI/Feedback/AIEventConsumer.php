<?php

declare(strict_types=1);

namespace App\Component\Product\AI\Feedback;

use Doctrine\DBAL\Connection;

final class AIEventConsumer
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function enqueue(array $event): void
    {
        $this->db->insert('ai_feedback_queue', [
            'ts' => gmdate('c'),
            'tenant_id' => $event['tenant_id'] ?? 'tenantA',
            'type' => $event['type'] ?? 'unknown',
            'payload' => json_encode($event, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }
}
