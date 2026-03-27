<?php

declare(strict_types=1);

namespace App\Component\Product\PublicAPI\RateLimit;

use Doctrine\DBAL\Connection;

final class WindowLimiter
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function hit(string $apiKey, int $limitPerMin = 120): bool
    {
        $window = gmdate('Y-m-d\TH:i:00\Z');
        $key = $apiKey.'|'.$window;
        $row = $this->db->fetchAssociative('SELECT count FROM ratelimit WHERE key = ?', [$key]);
        if (!$row) {
            $this->db->insert('ratelimit', ['key' => $key, 'count' => 1, 'window' => $window]);

            return true;
        }
        $count = (int) $row['count'] + 1;
        $this->db->update('ratelimit', ['count' => $count], ['key' => $key]);

        return $count <= $limitPerMin;
    }
}
