<?php

declare(strict_types=1);

namespace App\Applicating\RepositoryInterface;

use App\Applicating\Entity\Application;
use App\Applicating\Entity\ApplicationRuntimeAssignment;

interface ApplicationRuntimeAssignmentRepositoryInterface
{
    public function findOneForApplicationAndEnvironment(string $applicationSlug, string $environment): ?ApplicationRuntimeAssignment;

    public function findOneForApplicationEntityAndEnvironment(Application $application, string $environment): ?ApplicationRuntimeAssignment;
}
