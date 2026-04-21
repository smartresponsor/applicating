<?php

declare(strict_types=1);

namespace App\Application\Enum;

enum ApplicationInstallationState: string
{
    case Assigned = 'assigned';
    case Installed = 'installed';
    case Disabled = 'disabled';
    case Failed = 'failed';
}
