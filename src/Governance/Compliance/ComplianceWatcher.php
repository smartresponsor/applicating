<?php

declare(strict_types=1);

namespace App\Component\Product\Governance\Compliance;

use Doctrine\DBAL\Connection;

final class ComplianceWatcher
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Проверяет базовые SLA (ошибки репликации, латентность). */
    public function checkWindow(int $minutes = 60): array
    {
        $fails = (int) ($this->db->fetchOne("SELECT COUNT(*) FROM policy_replica_events WHERE ts>NOW()-INTERVAL ? AND status='fail'", [$minutes.' minutes']) ?? 0);
        $avgLat = (float) ($this->db->fetchOne('SELECT AVG(latency_ms) FROM policy_replica_events WHERE ts>NOW()-INTERVAL ? ', [$minutes.' minutes']) ?? 0.0);
        $ok = $fails < 5 and $avgLat < 500.0;

        return ['ok' => $ok, 'fails' => $fails, 'avg_latency_ms' => round($avgLat, 2)];
    }
}
