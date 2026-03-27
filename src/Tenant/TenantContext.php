<?php

declare(strict_types=1);

namespace App\Component\Product\Tenant;

final class TenantContext
{
    public function __construct(
        public string $id = 'public',
        public string $mode = 'rls', // 'rls' or 'schema'
    ) {
    }
}
