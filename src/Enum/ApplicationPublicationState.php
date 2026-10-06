<?php

declare(strict_types=1);

namespace App\Applicating\Enum;

/**
 * Defines the publication lifecycle states available to an application and its releases.
 */
enum ApplicationPublicationState: string
{
    case Draft = 'draft';
    case Moderation = 'moderation';
    case Published = 'published';
    case Suspended = 'suspended';
}
