<?php

declare(strict_types=1);

namespace App\Component\Product\Security\Tenant;

final class TenantIsolationMiddleware
{
    /** Простая проверка: ресурс принадлежит запрошенному tenant. */
    public function assertTenant(string $resourceTenant, string $requestTenant): void
    {
        if ($resourceTenant !== $requestTenant) {
            throw new \RuntimeException('Tenant isolation violation');
        }
    }
}
