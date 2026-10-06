<?php

declare(strict_types=1);

namespace App\Applicating\Entity;

use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Enum\ApplicationRuntimeMode;
use App\Applicating\Repository\ApplicationRuntimeAssignmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApplicationRuntimeAssignmentRepository::class)]
#[ORM\Table(
    name: 'application_runtime_assignment',
    indexes: [
        new ORM\Index(name: 'idx_application_runtime_assignment_application_id', columns: ['application_id']),
        new ORM\Index(name: 'idx_application_runtime_assignment_environment', columns: ['environment']),
        new ORM\Index(name: 'idx_application_runtime_assignment_mode', columns: ['runtime_mode']),
    ],
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'uniq_application_runtime_assignment', columns: ['application_id', 'environment']),
    ],
)]
/**
 * Persists one application's runtime mode assignment for a named deployment environment.
 */
class ApplicationRuntimeAssignmentEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ApplicationEntity::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ApplicationEntity $application;

    #[ORM\Column(length: 40)]
    private string $environment;

    #[ORM\Column(enumType: ApplicationRuntimeMode::class)]
    private ApplicationRuntimeMode $runtimeMode;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(ApplicationEntity $application, string $environment, ApplicationRuntimeMode $runtimeMode = ApplicationRuntimeMode::HostShared)
    {
        $environment = trim($environment);
        if ('' === $environment) {
            throw new \InvalidArgumentException('Application runtime environment must not be empty.');
        }

        $this->application = $application;
        $this->environment = $environment;
        $this->runtimeMode = $runtimeMode;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApplication(): ApplicationEntity
    {
        return $this->application;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function getRuntimeMode(): ApplicationRuntimeMode
    {
        return $this->runtimeMode;
    }

    /**
     * Changes the assigned runtime mode and refreshes the assignment timestamp when state actually changes.
     */
    public function changeRuntimeMode(ApplicationRuntimeMode $runtimeMode): void
    {
        if ($this->runtimeMode === $runtimeMode) {
            return;
        }

        $this->runtimeMode = $runtimeMode;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
