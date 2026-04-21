<?php

declare(strict_types=1);

namespace App\Application\Entity;

use App\Application\Enum\ApplicationAccessLevel;
use App\Application\Enum\ApplicationPublicationState;
use App\Application\Repository\ApplicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
#[ORM\Table(name: 'application_listing')]
#[ORM\HasLifecycleCallbacks]
class Application
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 160)]
    private string $name;

    #[ORM\Column(length: 120, unique: true)]
    private string $slug;

    #[ORM\Column(length: 160, unique: true)]
    private string $packageName;

    #[ORM\Column(length: 160)]
    private string $developerName;

    #[ORM\Column(length: 512)]
    private string $listingSummary;

    #[ORM\Column(enumType: ApplicationPublicationState::class)]
    private ApplicationPublicationState $publicationState = ApplicationPublicationState::Draft;

    #[ORM\Column(enumType: ApplicationAccessLevel::class)]
    private ApplicationAccessLevel $accessLevel = ApplicationAccessLevel::Public;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $billingCode = null;

    #[ORM\Column(length: 80)]
    private string $sandboxProfile = 'default';

    #[ORM\Column]
    private bool $enabledByDefault = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, ApplicationRelease> */
    #[ORM\OneToMany(targetEntity: ApplicationRelease::class, mappedBy: 'application', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $releases;

    /** @var Collection<int, ApplicationManifest> */
    #[ORM\OneToMany(targetEntity: ApplicationManifest::class, mappedBy: 'application', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $manifests;

    /** @var Collection<int, TenantApplication> */
    #[ORM\OneToMany(targetEntity: TenantApplication::class, mappedBy: 'application', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['assignedAt' => 'DESC'])]
    private Collection $tenantApplications;

    public function __construct(string $name, string $slug, string $packageName, string $developerName, string $listingSummary)
    {
        $this->name = $name;
        $this->slug = $slug;
        $this->packageName = $packageName;
        $this->developerName = $developerName;
        $this->listingSummary = $listingSummary;
        $this->releases = new ArrayCollection();
        $this->manifests = new ArrayCollection();
        $this->tenantApplications = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function rename(string $name): void
    {
        $this->name = $name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function changeSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getPackageName(): string
    {
        return $this->packageName;
    }

    public function changePackageName(string $packageName): void
    {
        $this->packageName = $packageName;
    }

    public function getDeveloperName(): string
    {
        return $this->developerName;
    }

    public function changeDeveloperName(string $developerName): void
    {
        $this->developerName = $developerName;
    }

    public function getListingSummary(): string
    {
        return $this->listingSummary;
    }

    public function changeListingSummary(string $listingSummary): void
    {
        $this->listingSummary = $listingSummary;
    }

    public function getPublicationState(): ApplicationPublicationState
    {
        return $this->publicationState;
    }

    public function markForModeration(): void
    {
        $this->publicationState = ApplicationPublicationState::Moderation;
    }

    public function publish(): void
    {
        $this->publicationState = ApplicationPublicationState::Published;
    }

    public function suspend(): void
    {
        $this->publicationState = ApplicationPublicationState::Suspended;
    }

    public function getAccessLevel(): ApplicationAccessLevel
    {
        return $this->accessLevel;
    }

    public function changeAccessLevel(ApplicationAccessLevel $accessLevel): void
    {
        $this->accessLevel = $accessLevel;
    }

    public function getBillingCode(): ?string
    {
        return $this->billingCode;
    }

    public function changeBillingCode(?string $billingCode): void
    {
        $this->billingCode = $billingCode;
    }

    public function getSandboxProfile(): string
    {
        return $this->sandboxProfile;
    }

    public function changeSandboxProfile(string $sandboxProfile): void
    {
        $this->sandboxProfile = $sandboxProfile;
    }

    public function isEnabledByDefault(): bool
    {
        return $this->enabledByDefault;
    }

    public function changeEnabledByDefault(bool $enabledByDefault): void
    {
        $this->enabledByDefault = $enabledByDefault;
    }

    /** @return Collection<int, ApplicationRelease> */
    public function getReleases(): Collection
    {
        return $this->releases;
    }

    public function addRelease(ApplicationRelease $release): void
    {
        if (!$this->releases->contains($release)) {
            $this->releases->add($release);
        }
    }

    /** @return Collection<int, ApplicationManifest> */
    public function getManifests(): Collection
    {
        return $this->manifests;
    }

    public function addManifest(ApplicationManifest $manifest): void
    {
        if (!$this->manifests->contains($manifest)) {
            $this->manifests->add($manifest);
        }
    }

    /** @return Collection<int, TenantApplication> */
    public function getTenantApplications(): Collection
    {
        return $this->tenantApplications;
    }

    public function addTenantApplication(TenantApplication $tenantApplication): void
    {
        if (!$this->tenantApplications->contains($tenantApplication)) {
            $this->tenantApplications->add($tenantApplication);
        }
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PrePersist]
    public function onCreate(): void
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function onUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
