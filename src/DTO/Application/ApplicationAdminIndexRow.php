<?php

declare(strict_types=1);

namespace App\DTO\Application;

use App\Entity\Application;

final class ApplicationAdminIndexRow
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $packageName,
        public readonly string $slug,
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
            packageName: $application->getPackageName(),
            slug: $application->getSlug(),
            publicationState: $application->getPublicationState()->value,
            accessLevel: $application->getAccessLevel()->value,
            releaseCount: $application->getReleases()->count(),
            tenantAssignmentCount: $application->getTenantApplications()->count(),
        );
    }
}
