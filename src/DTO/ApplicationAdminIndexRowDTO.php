<?php

declare(strict_types=1);

namespace App\Applicating\DTO;

use App\Applicating\Entity\Application;

final readonly class ApplicationAdminIndexRowDTO
{
    public function __construct(
        public int $id,
        public string $nameEntity,
        public string $packageName,
        public string $slug,
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
            packageName: $application->getPackageName(),
            slug: $application->getSlug(),
            publicationState: $application->getPublicationState()->value,
            accessLevel: $application->getAccessLevel()->value,
            releaseCount: $application->getReleases()->count(),
            tenantAssignmentCount: $application->getTenantApplications()->count(),
        );
    }
}
