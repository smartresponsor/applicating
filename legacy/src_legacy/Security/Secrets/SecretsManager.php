<?php

declare(strict_types=1);

namespace App\Component\Product\Security\Secrets;

use Doctrine\DBAL\Connection;

final class SecretsManager
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** @param array<string,mixed> $meta */
    public function put(string $key, string $ciphertext, array $meta = []): void
    {
        $this->db->insert('secrets_kv', [
            'key' => $key,
            'ciphertext' => $ciphertext,
            'meta' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'rotated_at' => gmdate('c'),
        ]);
    }

    public function get(string $key): ?array
    {
        $row = $this->db->fetchAssociative('SELECT * FROM secrets_kv WHERE key=? ORDER BY rotated_at DESC LIMIT 1', [$key]);

        return $row ?: null;
    }
}
