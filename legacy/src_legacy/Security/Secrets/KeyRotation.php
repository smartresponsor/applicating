<?php

declare(strict_types=1);

namespace App\Component\Product\Security\Secrets;

use Doctrine\DBAL\Connection;

final class KeyRotation
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function rotate(string $key): bool
    {
        // Демонстрация: просто отметим факт ротации
        $this->db->insert('key_rotations', [
            'key' => $key, 'rotated_at' => gmdate('c'), 'status' => 'ok',
        ]);

        return true;
    }
}
