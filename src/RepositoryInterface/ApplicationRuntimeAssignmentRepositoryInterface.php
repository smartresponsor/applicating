<?php

declare(strict_types=1);

namespace App\Applicating\RepositoryInterface;

use App\Applicating\Entity\ApplicationEntity;
use App\Applicating\Entity\ApplicationRuntimeAssignmentEntity;

interface ApplicationRuntimeAssignmentRepositoryInterface
{
    public function findOneForApplicationAndEnvironment(string $applicationSlug, string $environment): ?ApplicationRuntimeAssignmentEntity;

    public function findOneForApplicationEntityAndEnvironment(ApplicationEntity $application, string $environment): ?ApplicationRuntimeAssignmentEntity;

    public function save(ApplicationRuntimeAssignmentEntity $assignment): void;
}
