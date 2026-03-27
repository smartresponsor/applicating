<?php

declare(strict_types=1);

namespace App\Component\Product\SmartCloud;

use Doctrine\DBAL\Connection;

final class TenantService
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Создать арендатора с базовыми лимитами. */
    public function register(string $tenantId, string $plan = 'standard', string $region = 'us-east'): void
    {
        $this->db->insert('smartcloud_tenants', [
            'tenant_id' => $tenantId, 'plan' => $plan, 'region' => $region, 'created_at' => gmdate('c'),
            'status' => 'active',
        ]);
        $this->db->insert('smartcloud_limits', [
            'tenant_id' => $tenantId, 'monthly_limit_usd' => 100.0, 'qps_limit' => 50,
        ]);
    }

    public function setLimit(string $tenantId, float $usd, int $qps): void
    {
        $this->db->executeStatement('INSERT INTO smartcloud_limits(tenant_id,monthly_limit_usd,qps_limit) VALUES(?,?,?) ON CONFLICT(tenant_id) DO UPDATE SET monthly_limit_usd=EXCLUDED.monthly_limit_usd, qps_limit=EXCLUDED.qps_limit', [$tenantId, $usd, $qps]);
    }

    public function health(): array
    {
        $tenants = $this->db->fetchAllAssociative('SELECT tenant_id, plan, region, status FROM smartcloud_tenants ORDER BY tenant_id');

        return ['tenants' => $tenants, 'ts' => gmdate('c')];
    }
}
