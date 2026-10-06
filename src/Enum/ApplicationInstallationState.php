<?php

declare(strict_types=1);

namespace App\Applicating\Enum;

/**
 * Defines the tenant installation lifecycle states persisted for an application assignment.
 */
enum ApplicationInstallationState: string
{
    case Assigned = 'assigned';
    case Installed = 'installed';
    case Disabled = 'disabled';
    case Failed = 'failed';
}
