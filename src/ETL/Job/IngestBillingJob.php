<?php

declare(strict_types=1);

namespace App\Component\Product\ETL\Job;

use Doctrine\DBAL\Connection;

final class IngestBillingJob
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** @param array<int,array<string,mixed>> $rows */
    public function load(array $rows): int
    {
        $cnt = 0;
        foreach ($rows as $r) {
            $this->db->insert('staging_billing', [
                'tenant_id' => $r['tenant_id'] ?? 'tenantA',
                'invoice_id' => $r['invoice_id'] ?? ($r['id'] ?? 'unknown'),
                'amount_cents' => (int) ($r['amount_cents'] ?? $r['amount'] ?? 0),
                'currency' => (string) ($r['currency'] ?? 'usd'),
                'period_start' => (string) ($r['period_start'] ?? $r['start_date'] ?? date('Y-m-01')),
                'period_end' => (string) ($r['period_end'] ?? $r['end_date'] ?? date('Y-m-t')),
                'status' => (string) ($r['status'] ?? 'paid'),
            ]);
            ++$cnt;
        }

        return $cnt;
    }

    public function upsertFact(): int
    {
        $sql = "INSERT INTO fact_billing (tenant_id, period, amount_cents, currency, invoices)
                SELECT tenant_id, to_char(period_start::date,'YYYY-MM') as period, SUM(amount_cents), MAX(currency), COUNT(*)
                FROM staging_billing
                GROUP BY tenant_id, to_char(period_start::date,'YYYY-MM')
                ON CONFLICT (tenant_id, period) DO UPDATE
                SET amount_cents = EXCLUDED.amount_cents, invoices = EXCLUDED.invoices, currency = EXCLUDED.currency";

        return $this->db->executeStatement($sql);
    }
}
