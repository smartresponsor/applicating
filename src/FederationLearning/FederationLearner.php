<?php

declare(strict_types=1);

namespace App\Component\Product\FederationLearning;

use Doctrine\DBAL\Connection;

final class FederationLearner
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Агрегирует reward по регионам и обновляет локальные альфа/бета у policy_variant. */
    public function applyGlobalUpdate(string $policy, array $regionalStats): array
    {
        $updated = 0;
        foreach ($regionalStats as $region => $stats) {
            $variant = (string) ($stats['variant'] ?? '');
            $wins = (int) ($stats['wins'] ?? 0);
            $trials = (int) ($stats['trials'] ?? 0);
            if ('' === $variant || $trials <= 0) {
                continue;
            }

            $row = $this->db->fetchAssociative('SELECT * FROM policy_variant WHERE policy=? AND variant_id=?', [$policy, $variant]);
            if (!$row) {
                continue;
            }
            $alpha = (float) $row['alpha'] + max(0, $wins);
            $beta = (float) $row['beta'] + max(0, $trials - $wins);
            $this->db->update('policy_variant', ['alpha' => $alpha, 'beta' => $beta, 'trials' => ((int) $row['trials']) + $trials, 'wins' => ((int) $row['wins']) + $wins], ['id' => $row['id']]);
            ++$updated;
        }
        // записываем глобальный скор
        $this->db->insert('federation_learning_log', [
            'ts' => gmdate('c'), 'policy' => $policy, 'regions' => json_encode(array_keys($regionalStats)),
        ]);

        return ['ok' => true, 'updated' => $updated];
    }
}
