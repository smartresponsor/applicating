<?php

declare(strict_types=1);

namespace App\Component\Product\Dashboard\Service;

use App\Component\Product\Dashboard\DTO\ChartDTO;
use App\Component\Product\Dashboard\DTO\MetricDTO;
use Doctrine\DBAL\Connection;

final class DashboardAggregator
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** @return array<int,MetricDTO> */
    public function kpi(string $tenantId): array
    {
        $pred = (float) ($this->db->fetchOne('SELECT COALESCE(AVG(revenue_forecast),0) FROM abi_audit_events WHERE tenant_id = ?', [$tenantId]) ?: 0);
        $priceDelta = (float) ($this->db->fetchOne('SELECT COALESCE(AVG((computed_price_cents - base_price_cents)*1.0/base_price_cents),0) FROM price_events WHERE tenant_id = ?', [$tenantId]) ?: 0);
        $breaches = (int) ($this->db->fetchOne('SELECT COALESCE(SUM(CASE WHEN breach THEN 1 ELSE 0 END),0) FROM sla_events WHERE tenant_id = ?', [$tenantId]) ?: 0);
        $churnAvg = (float) ($this->db->fetchOne('SELECT COALESCE(AVG(churn_score),0) FROM customer_lifecycle_events WHERE tenant_id = ?', [$tenantId]) ?: 0);
        $winback = (float) ($this->db->fetchOne("SELECT COALESCE(AVG(CASE WHEN (details->>'result')='success' THEN 1 ELSE 0 END),0) FROM customer_lifecycle_events WHERE tenant_id = ? AND campaign='winback'", [$tenantId]) ?: 0);

        return [
            new MetricDTO('total_revenue_predicted', 'Прогноз выручки', round($pred, 2), 'u.e.'),
            new MetricDTO('avg_price_delta_pct', 'Δ цены, среднее', round($priceDelta * 100, 2), '%'),
            new MetricDTO('sla_breaches', 'Нарушения SLA', (float) $breaches, ''),
            new MetricDTO('churn_avg_score', 'Средний churn score', round($churnAvg, 3), ''),
            new MetricDTO('winback_success_rate', 'Winback success rate', round($winback * 100, 2), '%'),
        ];
    }

    /** @return array<int,ChartDTO> */
    public function charts(string $tenantId): array
    {
        $abi = $this->db->fetchAllAssociative("SELECT to_char(ts,'YYYY-MM-DD') t, revenue_forecast v FROM abi_audit_events WHERE tenant_id=? ORDER BY ts ASC LIMIT 90", [$tenantId]);
        $sla = $this->db->fetchAllAssociative("SELECT to_char(ts,'YYYY-MM-DD') t, credit_cents v FROM sla_events WHERE tenant_id=? ORDER BY ts ASC LIMIT 90", [$tenantId]);
        $churn = $this->db->fetchAllAssociative("SELECT to_char(ts,'YYYY-MM-DD') t, churn_score v FROM customer_lifecycle_events WHERE tenant_id=? ORDER BY ts ASC LIMIT 90", [$tenantId]);

        $m = fn ($row) => ['t' => $row['t'], 'v' => (float) $row['v']];

        return [
            new ChartDTO('abi_forecast', 'ABI: Прогноз выручки', array_map($m, $abi)),
            new ChartDTO('sla_credits', 'SLA: Начисленные кредиты', array_map($m, $sla)),
            new ChartDTO('churn', 'CLA: Churn score', array_map($m, $churn)),
        ];
    }
}
