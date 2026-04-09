<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\ApplicationPublicationState;
use App\Repository\ApplicationReleaseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApplicationReleaseRepository::class)]
#[ORM\Table(
    name: 'application_release',
    uniqueConstraints: [new ORM\UniqueConstraint(name: 'uniq_application_release_version_per_application', columns: ['application_id', 'version'])],
    indexes: [
        new ORM\Index(name: 'idx_application_release_application_id', columns: ['application_id']),
        new ORM\Index(name: 'idx_application_release_publication_state', columns: ['publication_state']),
    ],
)]
class ApplicationRelease
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Application::class, inversedBy: 'releases')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Application $application;

    #[ORM\Column(length: 32)]
    private string $version;

    #[ORM\Column(length: 32)]
    private string $channel;

    #[ORM\Column(length: 128)]
    private string $checksum;

    #[ORM\Column(length: 255)]
    private string $downloadUrl;

    #[ORM\Column(type: 'text')]
    private string $releaseNotes;

    #[ORM\Column(enumType: ApplicationPublicationState::class)]
    private ApplicationPublicationState $publicationState = ApplicationPublicationState::Draft;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    public function __construct(Application $application, string $version, string $channel, string $checksum, string $downloadUrl, string $releaseNotes)
    {
        $this->application = $application;
        $this->version = $version;
        $this->channel = $channel;
        $this->checksum = $checksum;
        $this->downloadUrl = $downloadUrl;
        $this->releaseNotes = $releaseNotes;
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

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getChecksum(): string
    {
        return $this->checksum;
    }

    public function getDownloadUrl(): string
    {
        return $this->downloadUrl;
    }

    public function getReleaseNotes(): string
    {
        return $this->releaseNotes;
    }

    public function getPublicationState(): ApplicationPublicationState
    {
        return $this->publicationState;
    }

    public function publish(): void
    {
        $this->publicationState = ApplicationPublicationState::Published;
        $this->publishedAt = new \DateTimeImmutable();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }
}
