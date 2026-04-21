<?php

declare(strict_types=1);

namespace App\Application\Enum;

enum ApplicationAccessLevel: string
{
    case Public = 'public';
    case Private = 'private';
    case TenantRestricted = 'tenant_restricted';
}
