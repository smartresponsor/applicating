<?php

declare(strict_types=1);

namespace App\Component\Product\AI;

use Doctrine\DBAL\Connection;

final class OrchestratorAI
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Прогноз QPS для арендатора на ближайший час (наивная модель скользящей средней). */
    public function predictQps(string $tenantId): float
    {
        $val = $this->db->fetchOne(
            "SELECT COALESCE(AVG(qps),0) FROM telemetry_qps WHERE tenant_id=? AND ts >= (NOW() - INTERVAL '1 hour')",
            [$tenantId]
        );
        $avg = (float) ($val ?? 0.0);

        // Простое "предиктивное" усиление тренда
        return round($avg * 1.15, 2);
    }

    /** Решение по масштабированию: вернуть желаемое количество реплик. */
    public function desiredReplicas(string $tenantId, int $currentReplicas): int
    {
        $qps = $this->predictQps($tenantId);
        $targetPerReplica = 100; // целевой QPS на реплику
        $need = max(1, (int) ceil($qps / $targetPerReplica));
        // Плавное изменение
        if ($need > $currentReplicas) {
            return $currentReplicas + 1;
        }
        if ($need < $currentReplicas) {
            return max(1, $currentReplicas - 1);
        }

        return $currentReplicas;
    }
}
