<?php

declare(strict_types=1);

namespace App\Component\Product\Sentient;

use Doctrine\DBAL\Connection;

final class DecisionTrainer
{
    public function __construct(private readonly Connection $db)
    {
    }

    /**
     * Обучает веса risk/trust на основе reward = sla_ok * 1.0 - risk * 0.7 + trust * 0.3.
     * Возвращает новую конфигурацию порогов.
     */
    public function train(int $minutes = 180): array
    {
        $rows = $this->db->fetchAllAssociative('SELECT sla_ok, risk, trust FROM sentient_feedback WHERE ts>NOW()-INTERVAL ? ', [$minutes.' minutes']);
        if (!$rows) {
            return ['auto' => 0.4, 'manual' => 0.7, 'weights' => ['risk' => 0.6, 'trust' => 0.3, 'sla' => 1.0]];
        }
        $sum = 0.0;
        $n = 0;
        foreach ($rows as $r) {
            $sla = (float) $r['sla_ok'];
            $risk = (float) $r['risk'];
            $trust = (float) $r['trust'];
            $reward = $sla * 1.0 - $risk * 0.7 + $trust * 0.3;
            $sum += $reward;
            ++$n;
        }
        $avg = $n ? $sum / $n : 0.5;
        // Чем выше средний reward, тем ниже пороги
        $auto = max(0.2, 0.6 - $avg * 0.2);
        $manual = max(0.5, 0.9 - $avg * 0.2);

        return ['auto' => round($auto, 2), 'manual' => round($manual, 2), 'weights' => ['risk' => 0.6, 'trust' => 0.3, 'sla' => 1.0]];
    }
}
