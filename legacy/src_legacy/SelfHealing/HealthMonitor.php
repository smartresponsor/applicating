<?php

declare(strict_types=1);

namespace App\Component\Product\SelfHealing;

use Doctrine\DBAL\Connection;

final class HealthMonitor
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function markIncident(string $tenantId, string $component, string $status, string $error, string $rule): void
    {
        $this->db->insert('healing_incidents', [
            'ts' => gmdate('c'),
            'tenant_id' => $tenantId,
            'component' => $component,
            'status' => $status,
            'error' => $error,
            'rule_applied' => $rule,
            'healed' => false,
            'retries' => 0,
        ]);
    }

    public function markHealed(int $id): void
    {
        $this->db->update('healing_incidents', ['healed' => true], ['id' => $id]);
    }
}
