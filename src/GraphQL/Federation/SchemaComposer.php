<?php

declare(strict_types=1);

namespace App\Component\Product\GraphQL\Federation;

use Doctrine\DBAL\Connection;

final class SchemaComposer
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Комбинирует SDL подфрагментов с gateway-схемой (наивно — конкатенация). */
    public function compose(string $gatewaySdl, ?string $filter = null): string
    {
        $rows = $this->db->fetchAllAssociative('SELECT sdl FROM gql_subgraphs');
        $parts = [$gatewaySdl];
        foreach ($rows as $r) {
            $parts[] = '
# --- subgraph ---
'.$r['sdl'];
        }

        return implode('
', $parts);
    }
}
