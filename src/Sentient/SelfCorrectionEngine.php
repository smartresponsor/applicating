<?php

declare(strict_types=1);

namespace App\Component\Product\Sentient;

use Doctrine\DBAL\Connection;

final class SelfCorrectionEngine
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Сохраняет новые пороги в таблицу и (опционально) дергает Orchestrator/Governance через webhooks. */
    public function adjust(array $thresholds): array
    {
        $this->db->insert('sentient_updates', [
            'ts' => gmdate('c'),
            'payload' => json_encode($thresholds, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        return ['ok' => true, 'applied' => $thresholds];
    }
}
