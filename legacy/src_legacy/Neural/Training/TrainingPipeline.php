<?php

declare(strict_types=1);

namespace App\Component\Product\Neural\Training;

use Doctrine\DBAL\Connection;

final class TrainingPipeline
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Сохраняет "локальные веса" (в демо — один коэффициент) для арендатора. */
    public function saveLocalWeights(string $tenantId, float $w): void
    {
        $this->db->insert('neural_local_weights', [
            'ts' => gmdate('c'), 'tenant_id' => $tenantId, 'weights' => json_encode(['w' => $w]),
        ]);
    }
}
