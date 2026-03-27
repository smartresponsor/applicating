<?php

declare(strict_types=1);

namespace App\Component\Product\GraphQL\Federation;

use Doctrine\DBAL\Connection;

final class SubgraphRegistry
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function register(string $name, string $url, string $sdl, string $version = '1'): void
    {
        $this->db->insert('gql_subgraphs', [
            'name' => $name, 'url' => $url, 'sdl' => $sdl, 'version' => $version, 'registered_at' => gmdate('c'),
        ]);
    }

    /** @return array<int,array<string,mixed>> */
    public function list(): array
    {
        return $this->db->fetchAllAssociative('SELECT * FROM gql_subgraphs ORDER BY name ASC');
    }
}
