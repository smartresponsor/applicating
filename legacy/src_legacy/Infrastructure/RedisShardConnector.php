<?php

declare(strict_types=1);

namespace App\Component\Product\Infrastructure;

final class RedisShardConnector
{
    /** Возвращает \Redis с подключением к DSN redis://host:port/db */
    public function connect(string $dsn): \Redis
    {
        $url = parse_url($dsn);
        $r = new \Redis();
        $r->connect($url['host'] ?? '127.0.0.1', (int) ($url['port'] ?? 6379));
        if (isset($url['path'])) {
            $db = (int) trim($url['path'], '/');
            $r->select($db);
        }

        return $r;
    }
}
