<?php

declare(strict_types=1);

namespace App\Applicating\Enum;

enum ApplicationRuntimeMode: string
{
    case HostShared = 'host_shared';
    case CustomDomain = 'custom_domain';
}
