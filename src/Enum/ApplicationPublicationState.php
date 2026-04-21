<?php

declare(strict_types=1);

namespace App\Application\Enum;

enum ApplicationPublicationState: string
{
    case Draft = 'draft';
    case Moderation = 'moderation';
    case Published = 'published';
    case Suspended = 'suspended';
}
