<?php

declare(strict_types=1);

namespace App\Applicating\Service;

use App\Applicating\Enum\ApplicationRuntimeMode;
use App\Applicating\RepositoryInterface\ApplicationRuntimeAssignmentRepositoryInterface;
use App\Applicating\ServiceInterface\ApplicationRuntimeAssignmentResolverInterface;

final readonly class ApplicationRuntimeAssignmentResolver implements ApplicationRuntimeAssignmentResolverInterface
{
    public function __construct(private ApplicationRuntimeAssignmentRepositoryInterface $repository)
    {
    }

    public function resolveMode(string $applicationSlug, string $environment): ApplicationRuntimeMode
    {
        $assignment = $this->repository->findOneForApplicationAndEnvironment($applicationSlug, $environment);

        return $assignment?->getRuntimeMode() ?? ApplicationRuntimeMode::HostShared;
    }
}
