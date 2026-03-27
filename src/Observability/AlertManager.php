<?php

declare(strict_types=1);

namespace App\Component\Product\Observability;

use Doctrine\DBAL\Connection;

final class AlertManager
{
    public function __construct(private readonly Connection $db)
    {
    }

    /**
     * Наивная проверка правила: value(name, last 5m) > threshold.
     *
     * @return array{fired:bool, value:float}
     */
    public function evaluate(string $name, float $threshold): array
    {
        $val = (float) ($this->db->fetchOne(
            "SELECT COALESCE(AVG(value),0) FROM metrics_timeseries WHERE name=? AND ts >= (NOW() - INTERVAL '5 minutes')",
            [$name]
        ) ?: 0.0);

        return ['fired' => $val > threshold, 'value' => $val];
    }

    public function createRule(string $key, string $expr, string $severity = 'warning'): void
    {
        $this->db->insert('alert_rules', [
            'key' => $key, 'expr' => $expr, 'severity' => $severity, 'enabled' => true, 'created_at' => gmdate('c'),
        ]);
    }

    /** @return array<int,array<string,mixed>> */
    public function listFiring(): array
    {
        return $this->db->fetchAllAssociative("SELECT * FROM alerts WHERE status='firing' ORDER BY started_at DESC LIMIT 50");
    }
}
