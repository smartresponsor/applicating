<?php

declare(strict_types=1);

namespace App\Component\Product\PolicyLearning;

use Doctrine\DBAL\Connection;

final class AdaptiveLearner
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function getOrCreateVariant(string $policy, string $variantId, array $effects): void
    {
        $exists = (int) $this->db->fetchOne('SELECT COUNT(*) FROM policy_variant WHERE policy=? AND variant_id=?', [$policy, $variantId]);
        if (0 === $exists) {
            $this->db->insert('policy_variant', [
                'policy' => $policy, 'variant_id' => $variantId, 'effects' => json_encode($effects, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'created_at' => gmdate('c'),
                'alpha' => 1.0, 'beta' => 1.0, 'wins' => 0, 'trials' => 0,
            ]);
        }
    }

    /** Простая Томпсоновская выборка по бете-распределению. */
    public function chooseVariant(string $policy): ?array
    {
        $rows = $this->db->fetchAllAssociative('SELECT * FROM policy_variant WHERE policy=?', [$policy]);
        if (!$rows) {
            return null;
        }
        $best = null;
        $bestScore = -1.0;
        foreach ($rows as $r) {
            $a = (float) $r['alpha'];
            $b = (float) $r['beta'];
            // псевдо-выборка бета (приближение): a/(a+b) с шумом
            $mean = $a / max(1e-9, $a + $b);
            $score = $mean + (mt_rand() / mt_getrandmax()) * 0.05;
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $r;
            }
        }

        return $best;
    }

    public function updatePosterior(string $policy, string $variantId, float $reward): void
    {
        $v = $this->db->fetchAssociative('SELECT * FROM policy_variant WHERE policy=? AND variant_id=?', [$policy, $variantId]);
        if (!$v) {
            return;
        }
        $alpha = (float) $v['alpha'];
        $beta = (float) $v['beta'];
        // бинаризуем reward в [0,1] (для простоты)
        $r = $reward > 0 ? 1.0 : 0.0;
        $alpha += $r;
        $beta += (1.0 - $r);
        $this->db->update('policy_variant', [
            'alpha' => $alpha, 'beta' => $beta, 'wins' => ((int) $v['wins']) + ($r > 0 ? 1 : 0), 'trials' => ((int) $v['trials']) + 1,
        ], ['id' => $v['id']]);
    }

    public function effectsForVariant(string $policy, string $variantId): array
    {
        $v = $this->db->fetchAssociative('SELECT effects FROM policy_variant WHERE policy=? AND variant_id=?', [$policy, $variantId]);

        return $v ? (json_decode($v['effects'], true) ?: []) : [];
    }
}
