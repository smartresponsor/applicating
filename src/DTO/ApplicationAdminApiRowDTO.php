<?php

declare(strict_types=1);

namespace App\Applicating\DTO;

use App\Applicating\Entity\Application\ApplicationEntity;

/**
 * Represents one application row exposed by the manager-facing administration API.
 */
final readonly class ApplicationAdminApiRowDTO
{
    public function __construct(
        public int $id,
        public string $nameEntity,
        public string $slug,
        public string $packageName,
        public string $developerName,
        public string $publicationState,
        public string $accessLevel,
        public int $releaseCount,
        public int $tenantAssignmentCount,
    ) {
    }

    /**
     * Projects persisted application state into the manager API row contract.
     */
    public static function fromApplication(ApplicationEntity $application): self
    {
        return new self(
            id: $application->getId() ?? 0,
            nameEntity: $application->getName(),
            slug: $application->getSlug(),
            packageName: $application->getPackageName(),
            developerName: $application->getDeveloperName(),
            publicationState: $application->getPublicationState()->value,
            accessLevel: $application->getAccessLevel()->value,
            releaseCount: $application->getReleases()->count(),
            tenantAssignmentCount: $application->getTenantApplications()->count(),
        );
    }

    /**
     * Returns the stable JSON-ready administration row shape.
     *
     * @return array{id:int,nameEntity:string,slug:string,packageName:string,developerName:string,publicationState:string,accessLevel:string,releaseCount:int,tenantAssignmentCount:int}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nameEntity' => $this->nameEntity,
            'slug' => $this->slug,
            'packageName' => $this->packageName,
            'developerName' => $this->developerName,
            'publicationState' => $this->publicationState,
            'accessLevel' => $this->accessLevel,
            'releaseCount' => $this->releaseCount,
            'tenantAssignmentCount' => $this->tenantAssignmentCount,
        ];
    }
}
