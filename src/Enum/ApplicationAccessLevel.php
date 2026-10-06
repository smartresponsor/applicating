<?php

declare(strict_types=1);

namespace App\Applicating\Enum;

/**
 * Defines the visibility boundary applied to an application's catalog access.
 */
enum ApplicationAccessLevel: string
{
    case Public = 'public';
    case Private = 'private';
    case TenantRestricted = 'tenant_restricted';
}
