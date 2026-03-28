<?php

declare(strict_types=1);

namespace App\Component\Product\SmartCloud;

use Doctrine\DBAL\Connection;

final class BillingDaemon
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Свод usage за вчера и биллинг. Возвращает число сформированных счетов. */
    public function daily(): int
    {
        $rows = $this->db->fetchAllAssociative("SELECT tenant_id, SUM(amount_usd) total FROM tenant_usage WHERE ts::date = (CURRENT_DATE - INTERVAL '1 day') GROUP BY tenant_id");
        $count = 0;
        foreach ($rows as $r) {
            $this->db->insert('tenant_invoices', [
                'tenant_id' => $r['tenant_id'],
                'period' => gmdate('Y-m-d', strtotime('-1 day')),
                'amount_usd' => $r['total'] ?? 0,
                'status' => 'pending',
                'created_at' => gmdate('c'),
            ]);
            ++$count;
        }

        return $count;
    }
}
