<?php

declare(strict_types=1);

namespace App\Component\Product\Neural;

use Doctrine\DBAL\Connection;

/**
 * Федеративная шина: агрегирует локальные веса агентов и распространяет обновления.
 */
final class FabricHub
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Примитивная "агрегация" — усреднение коэффициентов adjust из последних решений. */
    public function aggregate(): float
    {
        $avg = (float) ($this->db->fetchOne("SELECT COALESCE(AVG((decision->>'adjust')::numeric),1) FROM neural_agent_decisions WHERE ts >= (NOW()-INTERVAL '1 day')") ?? 1.0);
        $this->db->insert('neural_fabric_updates', ['ts' => gmdate('c'), 'global_adjust' => $avg]);

        return $avg;
    }

    /** Распространить глобальное значение в политики (заглушка). */
    public function broadcast(float $adjust): void
    {
        $this->db->insert('tenant_events', [
            'ts' => gmdate('c'), 'tenant_id' => '*', 'type' => 'fabric.broadcast', 'payload' => json_encode(['adjust' => $adjust]), 'status' => 'done',
        ]);
    }
}
