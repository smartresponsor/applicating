<?php

declare(strict_types=1);

namespace App\Component\Product\Tenant;

use Doctrine\DBAL\Connection;

final class TenantQuotaService
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function ensure(string $tenantId, int $limit): void
    {
        $this->db->executeStatement(
            "INSERT INTO tenant_quotas(tenant_id, period_start, period_end, request_limit, request_used) VALUES (:t, CURRENT_DATE, CURRENT_DATE + INTERVAL '30 days', :lim, 0)
             ON CONFLICT (tenant_id) DO NOTHING",
            ['t' => $tenantId, 'lim' => $limit]
        );
    }

    public function consume(string $tenantId, int $n = 1): bool
    {
        $row = $this->db->fetchAssociative('SELECT * FROM tenant_quotas WHERE tenant_id = :t', ['t' => $tenantId]);
        if (!$row) {
            return true;
        }
        if ((int) $row['request_used'] + $n > (int) $row['request_limit']) {
            return false;
        }
        $this->db->executeStatement('UPDATE tenant_quotas SET request_used = request_used + :n, updated_at = NOW() WHERE tenant_id = :t', ['n' => $n, 't' => $tenantId]);

        return true;
    }

    public function resetIfNeeded(string $tenantId): void
    {
        $this->db->executeStatement(
            "UPDATE tenant_quotas SET request_used = 0, period_start = CURRENT_DATE, period_end = CURRENT_DATE + INTERVAL '30 days' WHERE tenant_id = :t AND period_end <= CURRENT_DATE",
            ['t' => $tenantId]
        );
    }
}
