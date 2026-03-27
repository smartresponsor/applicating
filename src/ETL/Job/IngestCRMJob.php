<?php

declare(strict_types=1);

namespace App\Component\Product\ETL\Job;

use Doctrine\DBAL\Connection;

final class IngestCRMJob
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** @param array<int,array<string,mixed>> $events */
    public function load(array $events): int
    {
        $cnt = 0;
        foreach ($events as $e) {
            $this->db->insert('staging_crm', [
                'tenant_id' => $e['tenant_id'] ?? 'tenantA',
                'customer_id' => $e['customer_id'] ?? 'unknown',
                'event' => $e['event'] ?? 'unknown',
                'ts' => $e['ts'] ?? gmdate('c'),
                'meta' => json_encode($e['meta'] ?? []),
            ]);
            ++$cnt;
        }

        return $cnt;
    }

    public function syncDimCustomer(): int
    {
        $sql = 'INSERT INTO dim_customer (tenant_id, customer_id, last_event_ts)
                SELECT tenant_id, customer_id, MAX(ts) FROM staging_crm
                GROUP BY tenant_id, customer_id
                ON CONFLICT (tenant_id, customer_id) DO UPDATE
                SET last_event_ts = EXCLUDED.last_event_ts';

        return $this->db->executeStatement($sql);
    }
}
