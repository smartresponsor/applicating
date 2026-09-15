<?php

declare(strict_types=1);

namespace App\Applicating\DTO;

use App\Applicating\Entity\Application;

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

    public static function fromApplication(Application $application): self
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

    /** @return array{id:int,nameEntity:string,slug:string,packageName:string,developerName:string,publicationState:string,accessLevel:string,releaseCount:int,tenantAssignmentCount:int} */
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
