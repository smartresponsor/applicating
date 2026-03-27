<?php

declare(strict_types=1);

namespace App\Component\Product\Tenant;

use Doctrine\DBAL\Connection;

final class TenantRegistry
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** @return array<string,mixed>|null */
    public function get(string $tenantId): ?array
    {
        $row = $this->db->fetchAssociative('SELECT * FROM tenant_registry WHERE tenant_id=?', [$tenantId]);

        return $row ?: null;
    }

    /** @param array<string,mixed> $cfg */
    public function add(string $tenantId, array $cfg): void
    {
        $this->db->insert('tenant_registry', [
            'tenant_id' => $tenantId,
            'region' => $cfg['region'] ?? 'us-east',
            'pg_dsn' => $cfg['pg_dsn'] ?? 'pgsql:host=localhost;dbname=tenant',
            'redis_dsn' => $cfg['redis_dsn'] ?? 'redis://localhost:6379/0',
            'clickhouse_dsn' => $cfg['clickhouse_dsn'] ?? 'tcp://localhost:9000',
            'created_at' => gmdate('c'),
        ]);
    }
}
