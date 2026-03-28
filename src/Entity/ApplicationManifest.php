<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ApplicationManifestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApplicationManifestRepository::class)]
#[ORM\Table(name: 'application_manifest')]
class ApplicationManifest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Application::class, inversedBy: 'manifests')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Application $application;

    #[ORM\Column(length: 24)]
    private string $manifestVersion;

    #[ORM\Column(length: 160)]
    private string $identifier;

    /** @var list<string> */
    #[ORM\Column(type: 'json')]
    private array $capabilities;

    /** @var list<string> */
    #[ORM\Column(type: 'json')]
    private array $permissions;

    /** @var list<string> */
    #[ORM\Column(type: 'json')]
    private array $runtimeHooks;

    #[ORM\Column(length: 80)]
    private string $sandboxProfile;

    #[ORM\Column(length: 40)]
    private string $governanceState;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $rawManifest;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    /**
     * @param list<string>         $capabilities
     * @param list<string>         $permissions
     * @param list<string>         $runtimeHooks
     * @param array<string, mixed> $rawManifest
     */
    public function __construct(
        Application $application,
        string $manifestVersion,
        string $identifier,
        array $capabilities,
        array $permissions,
        array $runtimeHooks,
        string $sandboxProfile,
        string $governanceState,
        array $rawManifest,
    ) {
        $this->application = $application;
        $this->manifestVersion = $manifestVersion;
        $this->identifier = $identifier;
        $this->capabilities = $capabilities;
        $this->permissions = $permissions;
        $this->runtimeHooks = $runtimeHooks;
        $this->sandboxProfile = $sandboxProfile;
        $this->governanceState = $governanceState;
        $this->rawManifest = $rawManifest;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApplication(): Application
    {
        return $this->application;
    }

    public function getManifestVersion(): string
    {
        return $this->manifestVersion;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /** @return list<string> */
    public function getCapabilities(): array
    {
        return $this->capabilities;
    }

    /** @return list<string> */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /** @return list<string> */
    public function getRuntimeHooks(): array
    {
        return $this->runtimeHooks;
    }

    public function getSandboxProfile(): string
    {
        return $this->sandboxProfile;
    }

    public function getGovernanceState(): string
    {
        return $this->governanceState;
    }

    /** @return array<string, mixed> */
    public function getRawManifest(): array
    {
        return $this->rawManifest;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
