<?php

declare(strict_types=1);

namespace App\Component\Product\DeveloperHub;

use Doctrine\DBAL\Connection;

final class APIKeyManager
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function issue(string $developerId, string $label = 'default'): string
    {
        $key = bin2hex(random_bytes(24));
        $this->db->insert('devhub_api_keys', [
            'developer_id' => $developerId, 'label' => $label, 'api_key' => $key, 'created_at' => gmdate('c'), 'revoked' => false,
        ]);

        return $key;
    }

    public function revoke(string $apiKey): bool
    {
        $this->db->update('devhub_api_keys', ['revoked' => true, 'revoked_at' => gmdate('c')], ['api_key' => $apiKey]);

        return true;
    }

    public function validate(string $apiKey): bool
    {
        $row = $this->db->fetchAssociative('SELECT revoked FROM devhub_api_keys WHERE api_key=?', [$apiKey]);

        return $row && !$row['revoked'];
    }
}
