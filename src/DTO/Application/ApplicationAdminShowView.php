<?php

declare(strict_types=1);

namespace App\DTO\Application;

use App\Entity\Application;

final class ApplicationAdminShowView
{
    /**
     * @param list<ApplicationAdminReleaseView> $releases
     * @param list<ApplicationAdminManifestView> $manifests
     * @param list<ApplicationAdminTenantAssignmentView> $tenantAssignments
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $packageName,
        public readonly string $slug,
        public readonly string $listingSummary,
        public readonly string $publicationState,
        public readonly array $releases,
        public readonly array $manifests,
        public readonly array $tenantAssignments,
    ) {
    }

    /**
     * @param list<ApplicationAdminReleaseView> $releases
     * @param list<ApplicationAdminManifestView> $manifests
     * @param list<ApplicationAdminTenantAssignmentView> $tenantAssignments
     */
    public static function fromApplication(Application $application, array $releases, array $manifests, array $tenantAssignments): self
    {
        return new self(
            id: $application->getId() ?? 0,
            name: $application->getName(),
            packageName: $application->getPackageName(),
            slug: $application->getSlug(),
            listingSummary: $application->getListingSummary(),
            publicationState: $application->getPublicationState()->value,
            releases: $releases,
            manifests: $manifests,
            tenantAssignments: $tenantAssignments,
        );
    }
}
