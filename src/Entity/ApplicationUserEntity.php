<?php

declare(strict_types=1);

namespace App\Applicating\Entity;

use App\Applicating\Repository\ApplicationUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: ApplicationUserRepository::class)]
#[ORM\Table(
    name: 'application_user',
    indexes: [
        new ORM\Index(name: 'idx_application_user_active', columns: ['active']),
        new ORM\Index(name: 'idx_application_user_auth_source', columns: ['auth_source']),
    ],
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'uniq_application_user_identifier', columns: ['user_identifier']),
        new ORM\UniqueConstraint(name: 'uniq_application_user_email', columns: ['email']),
        new ORM\UniqueConstraint(name: 'uniq_application_user_external_subject', columns: ['external_subject']),
    ],
)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['userIdentifier'])]
/**
 * Persists local application-user identity, authentication source, roles, activity state, and login timestamps.
 */
class ApplicationUserEntity implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** @var non-empty-string */
    #[ORM\Column(name: 'user_identifier', length: 120)]
    private string $userIdentifier;

    #[ORM\Column(length: 160)]
    private string $displayName;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $email = null;

    /** @var list<string> */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(length: 32)]
    private string $authSource = 'local';

    #[ORM\Column(name: 'external_subject', length: 190, nullable: true)]
    private ?string $externalSubject = null;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastLoginAt = null;

    /** @param non-empty-string $userIdentifier */
    public function __construct(string $userIdentifier, string $displayName)
    {
        $this->userIdentifier = $userIdentifier;
        $this->displayName = $displayName;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /** @return non-empty-string */
    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }

    /**
     * Replaces the unique local user identifier used by Symfony security.
     *
     * @param non-empty-string $userIdentifier
     */
    public function renameIdentifier(string $userIdentifier): void
    {
        $this->userIdentifier = $userIdentifier;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * Replaces the human-facing display name associated with this local user.
     */
    public function renameDisplayName(string $displayName): void
    {
        $this->displayName = $displayName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Replaces the optional contact email associated with this local user.
     */
    public function changeEmail(?string $email): void
    {
        $this->email = $email;
    }

    /** @return list<string> */
    public function getRoles(): array
    {
        /** @var list<string> $roles */
        $roles = array_values(array_unique($this->roles));

        return $roles;
    }

    /** @param list<string> $roles */
    public function changeRoles(array $roles): void
    {
        $normalizedRoles = array_values(array_unique($roles));
        sort($normalizedRoles);
        $this->roles = $normalizedRoles;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Replaces the stored password hash used for local authentication.
     */
    public function changePassword(?string $password): void
    {
        $this->password = $password;
    }

    /**
     * Satisfies Symfony's transient-credential cleanup hook; no temporary credentials are stored.
     */
    public function eraseCredentials(): void
    {
    }

    public function getAuthSource(): string
    {
        return $this->authSource;
    }

    /**
     * Replaces the authentication-source identifier associated with this user.
     */
    public function changeAuthSource(string $authSource): void
    {
        $this->authSource = $authSource;
    }

    public function getExternalSubject(): ?string
    {
        return $this->externalSubject;
    }

    /**
     * Replaces the optional external-provider subject linked to this user.
     */
    public function changeExternalSubject(?string $externalSubject): void
    {
        $this->externalSubject = $externalSubject;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Marks the local user active for application access.
     */
    public function activate(): void
    {
        $this->active = true;
    }

    /**
     * Marks the local user inactive without deleting persisted identity state.
     */
    public function deactivate(): void
    {
        $this->active = false;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    /**
     * Records the most recent successful login time, defaulting to the current instant.
     */
    public function markLogin(?\DateTimeImmutable $loggedAt = null): void
    {
        $this->lastLoginAt = $loggedAt ?? new \DateTimeImmutable();
    }

    /**
     * Initializes persistence timestamps immediately before the user is first stored.
     */
    #[ORM\PrePersist]
    public function onCreate(): void
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    /**
     * Refreshes the persisted update timestamp immediately before Doctrine writes user changes.
     */
    #[ORM\PreUpdate]
    public function onUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
