<?php

declare(strict_types=1);

namespace App\Component\Product\AI\Feedback;

use App\Component\Product\AI\AnomalyDetector;
use App\Component\Product\AI\PolicyRewriter;
use Doctrine\DBAL\Connection;

final class AIFeedbackLoopService
{
    public function __construct(
        private readonly Connection $db,
        private readonly AnomalyDetector $det,
        private readonly PolicyRewriter $rew,
    ) {
    }

    public function processOne(): ?array
    {
        $row = $this->db->fetchAssociative('SELECT * FROM ai_feedback_queue ORDER BY ts ASC LIMIT 1');
        if (!$row) {
            return null;
        }
        $tenant = (string) $row['tenant_id'];
        $scan = $this->det->scan($tenant);
        $this->rew->rewrite($tenant, (bool) ($scan['anomaly'] ?? false));
        $this->db->executeStatement('DELETE FROM ai_feedback_queue WHERE id=?', [$row['id']]);

        return ['tenant' => $tenant, 'scan' => $scan];
    }
}
