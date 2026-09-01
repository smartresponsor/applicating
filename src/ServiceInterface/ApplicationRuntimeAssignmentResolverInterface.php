<?php

declare(strict_types=1);

namespace App\Applicating\ServiceInterface;

use App\Applicating\Enum\ApplicationRuntimeMode;

interface ApplicationRuntimeAssignmentResolverInterface
{
    public function resolveMode(string $applicationSlug, string $environment): ApplicationRuntimeMode;
}
