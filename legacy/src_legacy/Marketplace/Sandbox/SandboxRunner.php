<?php

declare(strict_types=1);

namespace App\Component\Product\Marketplace\Sandbox;

use Doctrine\DBAL\Connection;

final class SandboxRunner
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Демонстрация: записываем запуск в events, без реального Docker. */
    public function run(string $tenantId, int $pluginId, string $entry = 'run', array $args = []): array
    {
        $this->db->insert('plugin_events', [
            'ts' => gmdate('c'), 'tenant_id' => $tenantId, 'plugin_id' => $pluginId, 'event' => 'run', 'payload' => json_encode(['entry' => $entry, 'args' => $args]),
        ]);

        return ['ok' => true, 'started' => true];
    }
}
