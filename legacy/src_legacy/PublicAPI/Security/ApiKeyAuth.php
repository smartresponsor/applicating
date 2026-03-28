<?php

declare(strict_types=1);

namespace App\Component\Product\PublicAPI\Security;

use Doctrine\DBAL\Connection;

final class ApiKeyAuth
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function validate(?string $key): ?array
    {
        if (!$key) {
            return null;
        }
        $row = $this->db->fetchAssociative('SELECT * FROM api_keys WHERE api_key = ? AND disabled IS NOT TRUE', [$key]);

        return $row ?: null;
    }
}
