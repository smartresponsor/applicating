<?php

declare(strict_types=1);

namespace App\Component\Product\LLM;

use Doctrine\DBAL\Connection;

final class ContextBuilder
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Формирует компактный контекст на основе KPI ABI/CLA/SLA */
    public function build(string $tenantId): array
    {
        $revenueTrend = (float) ($this->db->fetchOne('SELECT COALESCE(AVG(revenue_forecast),0) FROM abi_audit_events WHERE tenant_id = ?', [$tenantId]) ?: 0);
        $churnAvg = (float) ($this->db->fetchOne('SELECT COALESCE(AVG(churn_score),0) FROM customer_lifecycle_events WHERE tenant_id = ?', [$tenantId]) ?: 0);
        $slaBreaches = (int) ($this->db->fetchOne('SELECT COALESCE(SUM(CASE WHEN breach THEN 1 ELSE 0 END),0) FROM sla_events WHERE tenant_id = ?', [$tenantId]) ?: 0);

        return [
            'tenant' => $tenantId,
            'abi' => ['revenue_trend' => $revenueTrend],
            'cla' => ['churn_avg' => $churnAvg],
            'sla' => ['breaches' => $slaBreaches],
        ];
    }
}
