<?php

declare(strict_types=1);

namespace App\Component\Product\PolicyFederation;

use Doctrine\DBAL\Connection;

final class PeerRegistry
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function list(): array
    {
        return $this->db->fetchAllAssociative('SELECT * FROM federation_peers ORDER BY region');
    }

    public function add(string $region, string $endpoint, string $pubkey): void
    {
        $this->db->insert('federation_peers', [
            'region' => $region, 'endpoint' => $endpoint, 'pubkey' => $pubkey, 'added_at' => gmdate('c'),
        ]);
    }
}
