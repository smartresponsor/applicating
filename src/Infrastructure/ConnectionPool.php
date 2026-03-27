<?php

declare(strict_types=1);

namespace App\Component\Product\Infrastructure;

use Doctrine\DBAL\Connection;

final class ConnectionPool
{
    /** @var array<string,Connection> */
    private array $pool = [];

    public function add(string $key, Connection $conn): void
    {
        $this->pool[$key] = $conn;
    }

    public function get(string $key): ?Connection
    {
        return $this->pool[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return isset($this->pool[$key]);
    }
}
