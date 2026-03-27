<?php

declare(strict_types=1);

namespace App\Component\Product\Infrastructure;

final class ClickhouseWriter
{
    /** Демонстрация: просто возвращаем payload. В проде — HTTP/Native клиент. */
    public function insert(string $dsn, string $table, array $rows): array
    {
        return ['ok' => true, 'dsn' => $dsn, 'table' => $table, 'rows' => count($rows)];
    }
}
