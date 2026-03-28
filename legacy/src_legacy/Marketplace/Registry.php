<?php

declare(strict_types=1);

namespace App\Component\Product\Marketplace;

use App\Component\Product\Marketplace\DTO\PluginManifest;
use Doctrine\DBAL\Connection;

final class Registry
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function publish(PluginManifest $m, string $sig): int
    {
        $this->db->insert('marketplace_plugins', [
            'name' => $m->name, 'author' => $m->author, 'latest_version' => $m->version, 'price_usd' => $m->price_usd,
            'permissions' => json_encode($m->permissions), 'meta' => json_encode($m->meta), 'published_at' => gmdate('c'),
        ]);
        $pid = (int) $this->db->lastInsertId();
        $this->db->insert('marketplace_releases', [
            'plugin_id' => $pid, 'version' => $m->version, 'manifest_json' => json_encode([
                'name' => $m->name, 'version' => $m->version, 'author' => $m->author, 'price_usd' => $m->price_usd,
                'permissions' => $m->permissions, 'meta' => $m->meta,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'signature' => $sig, 'created_at' => gmdate('c'),
        ]);

        return $pid;
    }

    /** @return array<int,array<string,mixed>> */
    public function list(): array
    {
        return $this->db->fetchAllAssociative('SELECT * FROM marketplace_plugins ORDER BY name ASC');
    }
}
