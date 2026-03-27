<?php

declare(strict_types=1);

namespace App\Component\Product\Tenant;

final class TenantResolver
{
    /** Выделяет tenant_id из субдомена или заголовка. */
    public function resolveFromHost(string $host): ?string
    {
        $parts = explode('.', $host);

        return count($parts) > 2 ? $parts[0] : null;
    }

    public function resolveFromHeader(?string $hdr): ?string
    {
        return $hdr ?: null;
    }
}
