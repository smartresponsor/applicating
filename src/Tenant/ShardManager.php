<?php

declare(strict_types=1);

namespace App\Component\Product\Tenant;

use Doctrine\DBAL\Connection;

final class ShardManager
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** @return array<int,array<string,mixed>> */
    public function listShards(string $region): array
    {
        return $this->db->fetchAllAssociative('SELECT * FROM tenant_shards WHERE region=? ORDER BY id', [$region]);
    }

    /** Простая функция: по хэшу tenant_id выбираем шард. */
    public function pickShard(string $tenantId, string $region = 'us-east'): ?array
    {
        $shards = $this->listShards($region);
        if (!$shards) {
            return null;
        }
        $h = hexdec(substr(hash('xxh3', $tenantId), 0, 8));
        $idx = $h % count($shards);

        return $shards[$idx] ?? $shards[0];
    }
}
