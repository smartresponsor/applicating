<?php

declare(strict_types=1);

namespace App\Applicating\ServiceInterface;

use App\Applicating\Entity\ApplicationRuntimeAssignmentEntity;
use App\Applicating\Enum\ApplicationRuntimeMode;

interface ApplicationRuntimeAssignmentServiceInterface
{
    public function setMode(string $applicationSlug, string $environment, ApplicationRuntimeMode $runtimeMode): ApplicationRuntimeAssignmentEntity;
}
