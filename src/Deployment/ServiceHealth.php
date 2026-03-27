<?php

declare(strict_types=1);

namespace App\Component\Product\Deployment;

use Doctrine\DBAL\Connection;

final class ServiceHealth
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** @return array<int,array<string,mixed>> */
    public function check(array $services): array
    {
        $out = [];
        foreach ($services as $name => $url) {
            $ok = false !== @file_get_contents($url);
            $out[] = ['service' => $name, 'url' => $url, 'ok' => $ok];
            $this->db->insert('deploy_health', [
                'ts' => gmdate('c'), 'service' => $name, 'ok' => $ok, 'url' => $url,
            ]);
        }

        return $out;
    }
}
