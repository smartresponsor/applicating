<?php

declare(strict_types=1);

namespace App\Applicating\Service;

use App\Applicating\Entity\ApplicationRuntimeAssignmentEntity;
use App\Applicating\Enum\ApplicationRuntimeMode;
use App\Applicating\RepositoryInterface\ApplicationRepositoryInterface;
use App\Applicating\RepositoryInterface\ApplicationRuntimeAssignmentRepositoryInterface;
use App\Applicating\ServiceInterface\ApplicationRuntimeAssignmentServiceInterface;

final readonly class ApplicationRuntimeAssignmentService implements ApplicationRuntimeAssignmentServiceInterface
{
    public function __construct(
        private ApplicationRepositoryInterface $applicationRepository,
        private ApplicationRuntimeAssignmentRepositoryInterface $runtimeAssignmentRepository,
    ) {
    }

    public function setMode(string $applicationSlug, string $environment, ApplicationRuntimeMode $runtimeMode): ApplicationRuntimeAssignmentEntity
    {
        $applicationSlug = trim($applicationSlug);
        $environment = trim($environment);
        if ('' === $applicationSlug || '' === $environment) {
            throw new \InvalidArgumentException('Application slug and environment must not be empty.');
        }

        $application = $this->applicationRepository->findOneBySlug($applicationSlug);
        if (null === $application) {
            throw new \DomainException(sprintf('Application "%s" was not found.', $applicationSlug));
        }

        $assignment = $this->runtimeAssignmentRepository->findOneForApplicationEntityAndEnvironment($application, $environment);
        if (null === $assignment) {
            $assignment = new ApplicationRuntimeAssignmentEntity($application, $environment, $runtimeMode);
        } else {
            $assignment->changeRuntimeMode($runtimeMode);
        }

        $this->runtimeAssignmentRepository->save($assignment);

        return $assignment;
    }
}
