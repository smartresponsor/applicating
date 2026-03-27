<?php

declare(strict_types=1);

namespace App\Component\Product\ETL\Job;

use Doctrine\DBAL\Connection;

final class CDCReplicator
{
    public function __construct(private readonly Connection $db)
    {
    }

    /**
     * Имитация CDC: перенос новых staging_* строк в журнал cdc_log.
     */
    public function replicate(): int
    {
        $total = 0;
        foreach (['staging_usage', 'staging_billing', 'staging_crm'] as $tbl) {
            $sql = "INSERT INTO cdc_log (ts, table_name, row_count)
                    SELECT NOW(), :tbl, COUNT(*) FROM {$tbl}
                    WHERE processed IS NOT TRUE";
            $total += $this->db->executeStatement($sql, ['tbl' => $tbl]);
            $this->db->executeStatement("UPDATE {$tbl} SET processed = TRUE WHERE processed IS NOT TRUE");
        }

        return $total;
    }
}
