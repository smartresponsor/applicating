<?php

declare(strict_types=1);

namespace App\Application\DTO\Application;

use App\Application\Entity\Application;

final readonly class ApplicationAdminShowView
{
    /**
     * @param list<ApplicationAdminReleaseView>          $releases
     * @param list<ApplicationAdminManifestView>         $manifests
     * @param list<ApplicationAdminTenantAssignmentView> $tenantAssignments
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $packageName,
        public string $slug,
        public string $listingSummary,
        public string $publicationState,
        public array $releases,
        public array $manifests,
        public array $tenantAssignments,
    ) {
    }

    /**
     * @param list<ApplicationAdminReleaseView>          $releases
     * @param list<ApplicationAdminManifestView>         $manifests
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
