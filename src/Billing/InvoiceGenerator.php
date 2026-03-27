<?php

declare(strict_types=1);

namespace App\Component\Product\Billing;

use Doctrine\DBAL\Connection;

final class InvoiceGenerator
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Формирует счёт за период для арендатора и замораживает сумму. */
    public function generate(string $tenantId, string $period): int
    {
        $rows = $this->db->fetchAllAssociative(
            "SELECT SUM(amount_usd) as total FROM billing_usage WHERE tenant_id=? AND to_char(ts,'YYYY-MM')=?",
            [$tenantId, $period]
        );
        $total = (float) ($rows[0]['total'] ?? 0.0);
        $this->db->insert('billing_invoices', [
            'tenant_id' => $tenantId, 'period' => $period, 'amount_usd' => $total, 'status' => 'pending', 'created_at' => gmdate('c'),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function markPaid(int $invoiceId, string $method, float $amount): void
    {
        $this->db->update('billing_invoices', ['status' => 'paid', 'paid_at' => gmdate('c')], ['id' => $invoiceId]);
        $this->db->insert('billing_payments', [
            'invoice_id' => $invoiceId, 'method' => $method, 'amount_usd' => $amount, 'ts' => gmdate('c'),
        ]);
    }
}
