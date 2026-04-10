<?php

declare(strict_types=1);

namespace App\DTO\Application;

use App\Entity\Application;

final readonly class ApplicationAdminIndexRow
{
    public function __construct(
        public int $id,
        public string $name,
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
            name: $application->getName(),
            packageName: $application->getPackageName(),
            slug: $application->getSlug(),
            publicationState: $application->getPublicationState()->value,
            accessLevel: $application->getAccessLevel()->value,
            releaseCount: $application->getReleases()->count(),
            tenantAssignmentCount: $application->getTenantApplications()->count(),
        );
    }
}
