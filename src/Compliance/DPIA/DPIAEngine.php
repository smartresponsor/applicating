<?php

declare(strict_types=1);

namespace App\Component\Product\Compliance\DPIA;

use Doctrine\DBAL\Connection;

final class DPIAEngine
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function evaluate(string $process, array $factors): array
    {
        $risk = 0.0;
        $risk += (float) ($factors['data_sensitivity'] ?? 0.3) * 0.5;
        $risk += (float) ($factors['volume'] ?? 0.2) * 0.3;
        $risk += (float) ($factors['third_parties'] ?? 0.1) * 0.2;
        $risk = max(0.0, min(1.0, $risk));
        $this->db->insert('compliance_dpia', [
            'ts' => gmdate('c'),
            'process' => $process,
            'risk_score' => $risk,
            'factors' => json_encode($factors),
        ]);

        return ['process' => $process, 'risk_score' => $risk];
    }
}
