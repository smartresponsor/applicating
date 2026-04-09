<?php

declare(strict_types=1);

namespace App\DTO\Application;

use App\Entity\Application;

final class ApplicationAdminApiRow
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $packageName,
        public readonly string $developerName,
        public readonly string $publicationState,
        public readonly string $accessLevel,
        public readonly int $releaseCount,
        public readonly int $tenantAssignmentCount,
    ) {
    }

    public static function fromApplication(Application $application): self
    {
        return new self(
            id: $application->getId() ?? 0,
            name: $application->getName(),
            slug: $application->getSlug(),
            packageName: $application->getPackageName(),
            developerName: $application->getDeveloperName(),
            publicationState: $application->getPublicationState()->value,
            accessLevel: $application->getAccessLevel()->value,
            releaseCount: $application->getReleases()->count(),
            tenantAssignmentCount: $application->getTenantApplications()->count(),
        );
    }

    /** @return array{id:int,name:string,slug:string,packageName:string,developerName:string,publicationState:string,accessLevel:string,releaseCount:int,tenantAssignmentCount:int} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
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
