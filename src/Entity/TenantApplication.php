<?php

declare(strict_types=1);

namespace App\Applicating\Entity;

use App\Applicating\Enum\ApplicationInstallationState;
use App\Applicating\Repository\TenantApplicationRepository;
use App\Objecting\EntityInterface\ObjectRelationEntityInterface;
use App\Objecting\EntityTrait\Embeddable\ObjectAuditEmbeddableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TenantApplicationRepository::class)]
#[ORM\Table(
    name: 'tenant_application',
    indexes: [
        new ORM\Index(name: 'idx_tenant_application_application_id', columns: ['application_id']),
        new ORM\Index(name: 'idx_tenant_application_tenant_key', columns: ['tenant_key']),
        new ORM\Index(name: 'idx_tenant_application_installation_state', columns: ['installation_state']),
    ],
    uniqueConstraints: [new ORM\UniqueConstraint(name: 'uniq_tenant_application_assignment', columns: ['application_id', 'tenant_key'])],
)]
class TenantApplication implements ObjectRelationEntityInterface
{
    use ObjectAuditEmbeddableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Application::class, inversedBy: 'tenantApplications')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Application $application;

    #[ORM\Column(length: 120)]
    private string $tenantKey;

    #[ORM\Column(length: 32)]
    private string $installedVersion;

    #[ORM\Column(enumType: ApplicationInstallationState::class)]
    private ApplicationInstallationState $installationState;

    #[ORM\Column]
    private bool $enabled;

    #[ORM\Column]
    private bool $billingActive;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $accessPolicy;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $diagnostics;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $assignedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $installedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastCheckedAt = null;

    /** @param array<string, mixed> $accessPolicy */
    public function __construct(Application $application, string $tenantKey, string $installedVersion, bool $enabled, bool $billingActive, array $accessPolicy)
    {
        $this->application = $application;
        $this->tenantKey = $tenantKey;
        $this->installedVersion = $installedVersion;
        $this->enabled = $enabled;
        $this->billingActive = $billingActive;
        $this->accessPolicy = $accessPolicy;
        $this->diagnostics = [];
        $this->installationState = $enabled ? ApplicationInstallationState::Installed : ApplicationInstallationState::Assigned;
        $this->assignedAt = new \DateTimeImmutable();
        $this->installedAt = $enabled ? new \DateTimeImmutable() : null;
        $this->initializeObjectAudit($this->assignedAt);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApplication(): Application
    {
        return $this->application;
    }

    public function getTenantKey(): string
    {
        return $this->tenantKey;
    }

    public function getInstalledVersion(): string
    {
        return $this->installedVersion;
    }

    /** @param array<string, mixed> $accessPolicy */
    public function updateAssignment(string $installedVersion, bool $enabled, bool $billingActive, array $accessPolicy): void
    {
        $this->installedVersion = $installedVersion;
        $this->billingActive = $billingActive;
        $this->accessPolicy = $accessPolicy;

        if ($enabled) {
            $this->enable();

            return;
        }

        $this->disable();
    }

    public function getInstallationState(): ApplicationInstallationState
    {
        return $this->installationState;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isBillingActive(): bool
    {
        return $this->billingActive;
    }

    /** @return array<string, mixed> */
    public function getAccessPolicy(): array
    {
        return $this->accessPolicy;
    }

    /** @return array<string, mixed> */
    public function getDiagnostics(): array
    {
        return $this->diagnostics;
    }

    /** @param array<string, mixed> $diagnostics */
    public function setDiagnostics(array $diagnostics): void
    {
        $this->diagnostics = $diagnostics;
        $this->lastCheckedAt = new \DateTimeImmutable();
    }

    public function enable(): void
    {
        $this->enabled = true;
        $this->installationState = ApplicationInstallationState::Installed;
        $this->installedAt ??= new \DateTimeImmutable();
    }

    public function disable(): void
    {
        $this->enabled = false;
        $this->installationState = ApplicationInstallationState::Disabled;
    }

    public function getAssignedAt(): \DateTimeImmutable
    {
        return $this->assignedAt;
    }

    public function getInstalledAt(): ?\DateTimeImmutable
    {
        return $this->installedAt;
    }

    public function getLastCheckedAt(): ?\DateTimeImmutable
    {
        return $this->lastCheckedAt;
    }
}
