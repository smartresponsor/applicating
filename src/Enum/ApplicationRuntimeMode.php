<?php

declare(strict_types=1);

namespace App\Applicating\Enum;

/**
 * Defines the supported runtime-placement modes for an application environment assignment.
 */
enum ApplicationRuntimeMode: string
{
    case HostShared = 'host_shared';
    case CustomDomain = 'custom_domain';
}
