<?php

declare(strict_types=1);

namespace App\Component\Product\SmartCloud;

use Doctrine\DBAL\Connection;

final class Orchestrator
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Зарегистрировать событие в облачной оркестрации (tenant-aware). */
    public function emit(string $tenantId, string $type, array $payload = []): int
    {
        $this->db->insert('tenant_events', [
            'ts' => gmdate('c'), 'tenant_id' => $tenantId, 'type' => $type,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'status' => 'queued',
        ]);

        return (int) $this->db->lastInsertId();
    }

    /** Простая стратегия failover: пометить инцидент и переключить регион. */
    public function failover(string $tenantId, string $regionFrom, string $regionTo): void
    {
        $this->db->insert('tenant_events', [
            'ts' => gmdate('c'), 'tenant_id' => $tenantId, 'type' => 'failover',
            'payload' => json_encode(['from' => $regionFrom, 'to' => $regionTo]), 'status' => 'done',
        ]);
        $this->db->update('smartcloud_tenants', ['region' => $regionTo], ['tenant_id' => $tenantId]);
    }
}
