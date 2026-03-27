<?php

declare(strict_types=1);

namespace App\Component\Product\Tenant;

use Doctrine\DBAL\Connection;

final class MigrationHelper
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Выполнить SQL-файл на подключении (упрощённо). */
    public function runSqlFile(string $path): int
    {
        $sql = file_get_contents($path) ?: '';
        if (!$sql) {
            return 0;
        }

        return $this->db->executeStatement($sql);
    }
}
