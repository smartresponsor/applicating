<?php

declare(strict_types=1);

namespace App\Component\Product\Infrastructure;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

final class PgShardConnector
{
    /** Подключение к конкретному DSN (простой helper). */
    public function connectDsn(string $dsn, ?string $user = null, ?string $pass = null): Connection
    {
        return DriverManager::getConnection(['url' => $dsn, 'user' => $user, 'password' => $pass]);
    }
}
