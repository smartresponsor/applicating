<?php

declare(strict_types=1);

namespace App\Applicating\Entity;

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
class ApplicationRuntimeAssignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Application::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Application $application;

    #[ORM\Column(length: 40)]
    private string $environment;

    #[ORM\Column(enumType: ApplicationRuntimeMode::class)]
    private ApplicationRuntimeMode $runtimeMode;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(Application $application, string $environment, ApplicationRuntimeMode $runtimeMode = ApplicationRuntimeMode::HostShared)
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

    public function getApplication(): Application
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
