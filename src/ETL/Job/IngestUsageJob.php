<?php

declare(strict_types=1);

namespace App\Component\Product\ETL\Job;

use App\Component\Product\ETL\Connector\UsageJSONConnector;
use Doctrine\DBAL\Connection;

final class IngestUsageJob
{
    public function __construct(private readonly Connection $db, private readonly UsageJSONConnector $conn)
    {
    }

    /** @param array<int,array<string,mixed>> $rows */
    public function load(array $rows): int
    {
        $cnt = 0;
        foreach ($rows as $r) {
            $this->db->insert('staging_usage', [
                'tenant_id' => $r['tenant_id'] ?? 'tenantA',
                'ts' => $r['ts'] ?? gmdate('c'),
                'metric' => $r['metric'] ?? 'requests',
                'value' => $r['value'] ?? 0,
            ]);
            ++$cnt;
        }

        return $cnt;
    }

    public function rollupDaily(string $tenantId): int
    {
        // Простейший daily rollup
        $sql = "INSERT INTO fact_usage_daily (d, tenant_id, metric, value)
                SELECT date_trunc('day', ts)::date, tenant_id, metric, SUM(value)
                FROM staging_usage
                WHERE tenant_id = :t
                GROUP BY 1,2,3
                ON CONFLICT (d, tenant_id, metric) DO UPDATE SET value = EXCLUDED.value";

        return $this->db->executeStatement($sql, ['t' => $tenantId]);
    }
}
