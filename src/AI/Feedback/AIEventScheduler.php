<?php

declare(strict_types=1);

namespace App\Component\Product\AI\Feedback;

use Doctrine\DBAL\Connection;

final class AIEventScheduler
{
    public function __construct(private readonly Connection $db, private readonly AIFeedbackLoopService $loop)
    {
    }

    /** Возьмём последние N событий и прогоним через loop. */
    public function run(int $batch = 5): array
    {
        $events = $this->db->fetchAllAssociative("SELECT id, tenant_id, type FROM tenant_events WHERE ts >= (NOW() - INTERVAL '1 hour') ORDER BY ts DESC LIMIT ?", [$batch]);
        $processed = [];
        foreach ($events as $ev) {
            $this->db->insert('ai_feedback_queue', [
                'ts' => gmdate('c'), 'tenant_id' => $ev['tenant_id'], 'type' => $ev['type'], 'payload' => json_encode($ev),
            ]);
            $p = $this->loop->processOne();
            if ($p) {
                $processed[] = $p;
            }
        }

        return $processed;
    }
}
