<?php

declare(strict_types=1);

namespace App\Applicating\Service;

use App\Applicating\Entity\ApplicationRuntimeAssignment;
use App\Applicating\Enum\ApplicationRuntimeMode;
use App\Applicating\Repository\ApplicationRepository;
use App\Applicating\RepositoryInterface\ApplicationRuntimeAssignmentRepositoryInterface;
use App\Applicating\ServiceInterface\ApplicationRuntimeAssignmentServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ApplicationRuntimeAssignmentService implements ApplicationRuntimeAssignmentServiceInterface
{
    public function __construct(
        private ApplicationRepository $applicationRepository,
        private ApplicationRuntimeAssignmentRepositoryInterface $runtimeAssignmentRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function setMode(string $applicationSlug, string $environment, ApplicationRuntimeMode $runtimeMode): ApplicationRuntimeAssignment
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
            $assignment = new ApplicationRuntimeAssignment($application, $environment, $runtimeMode);
            $this->entityManager->persist($assignment);
        } else {
            $assignment->changeRuntimeMode($runtimeMode);
        }

        $this->entityManager->flush();

        return $assignment;
    }
}
