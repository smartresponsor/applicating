<?php

declare(strict_types=1);

namespace App\Applicating\Entity;

use App\Applicating\Enum\ApplicationAccessLevel;
use App\Applicating\Enum\ApplicationPublicationState;
use App\Applicating\Repository\ApplicationRepository;
use App\Objecting\EntityInterface\ObjectEntityInterface;
use App\Objecting\EntityTrait\Embeddable\ObjectAuditEmbeddableTrait;
use App\Objecting\EntityTrait\Embeddable\ObjectIdentityEmbeddableTrait;
use App\Objecting\EntityTrait\Embeddable\ObjectStateEmbeddableTrait;
use App\Objecting\EntityTrait\Embeddable\ObjectTitleEmbeddableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
#[ORM\Table(
    name: 'application_listing',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'uniq_application_listing_slug', columns: ['slug']),
        new ORM\UniqueConstraint(name: 'uniq_application_listing_package_name', columns: ['package_name']),
    ],
)]
#[ORM\HasLifecycleCallbacks]
class Application implements ObjectEntityInterface
{
    use ObjectIdentityEmbeddableTrait;
    use ObjectTitleEmbeddableTrait;
    use ObjectAuditEmbeddableTrait;
    use ObjectStateEmbeddableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 160)]
    private string $nameEntity;

    #[ORM\Column(length: 120)]
    private string $slug;

    #[ORM\Column(length: 160)]
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

    public function __construct(string $nameEntity, string $slug, string $packageName, string $developerName, string $listingSummary)
    {
        $this->nameEntity = $nameEntity;
        $this->slug = $slug;
        $this->packageName = $packageName;
        $this->developerName = $developerName;
        $this->listingSummary = $listingSummary;
        $this->releases = new ArrayCollection();
        $this->manifests = new ArrayCollection();
        $this->tenantApplications = new ArrayCollection();
        $now = new \DateTimeImmutable();
        $this->initializeObjectIdentity(objectSlug: $slug);
        $this->initializeObjectTitle($nameEntity);
        $this->initializeObjectAudit($now);
        $this->touchModified($now);
        $this->initializeObjectState(objectStatus: ApplicationPublicationState::Draft->value);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->nameEntity;
    }

    public function rename(string $nameEntity): void
    {
        $this->nameEntity = $nameEntity;
        $this->setFirstTitle($nameEntity);
        $this->touchModified();
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function changeSlug(string $slug): void
    {
        $this->slug = $slug;
        $this->setObjectSlug($slug);
        $this->touchModified();
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
        $this->setObjectStatus(ApplicationPublicationState::Moderation->value);
        $this->touchModified();
    }

    public function publish(): void
    {
        $this->publicationState = ApplicationPublicationState::Published;
        $this->setObjectStatus(ApplicationPublicationState::Published->value);
        $this->setObjectActive(true);
        $this->touchModified();
    }

    public function suspend(): void
    {
        $this->publicationState = ApplicationPublicationState::Suspended;
        $this->setObjectStatus(ApplicationPublicationState::Suspended->value);
        $this->setObjectActive(false);
        $this->touchModified();
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

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->getModifiedAt() ?? $this->getCreatedAt();
    }

    #[ORM\PrePersist]
    public function onCreate(): void
    {
        if (null === $this->getModifiedAt()) {
            $this->touchModified($this->getCreatedAt());
        }
    }

    #[ORM\PreUpdate]
    public function onUpdate(): void
    {
        $this->touchModified();
    }
}
