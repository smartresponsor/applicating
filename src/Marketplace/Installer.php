<?php

declare(strict_types=1);

namespace App\Component\Product\Marketplace;

use Doctrine\DBAL\Connection;

final class Installer
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function install(string $tenantId, int $pluginId, string $version): bool
    {
        $this->db->insert('tenant_plugins', [
            'tenant_id' => $tenantId, 'plugin_id' => $pluginId, 'version' => $version, 'status' => 'installed', 'installed_at' => gmdate('c'),
        ]);

        return true;
    }

    public function uninstall(string $tenantId, int $pluginId): bool
    {
        $this->db->update('tenant_plugins', ['status' => 'removed'], ['tenant_id' => $tenantId, 'plugin_id' => $pluginId]);

        return true;
    }
}
