<?php

declare(strict_types=1);

namespace App\Component\Product\Security\Retention;

use Doctrine\DBAL\Connection;

final class DataRetention
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function apply(string $policyKey): int
    {
        // Демонстрация: удалим старые метрики старше 90 дней
        $n = $this->db->executeStatement("DELETE FROM metrics_timeseries WHERE ts < (NOW() - INTERVAL '90 days')");
        $this->db->insert('retention_runs', ['ts' => gmdate('c'), 'policy_key' => $policyKey, 'deleted_rows' => $n]);

        return $n;
    }
}
