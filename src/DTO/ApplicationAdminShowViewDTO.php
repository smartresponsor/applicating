<?php

declare(strict_types=1);

namespace App\Applicating\DTO;

use App\Applicating\Entity\Application\ApplicationEntity;

/**
 * Aggregates the administration detail projection for one application and its lifecycle relations.
 */
final readonly class ApplicationAdminShowViewDTO
{
    /**
     * @param list<ApplicationAdminReleaseViewDTO>          $releases
     * @param list<ApplicationAdminManifestViewDTO>         $manifests
     * @param list<ApplicationAdminTenantAssignmentViewDTO> $tenantAssignments
     */
    public function __construct(
        public int $id,
        public string $nameEntity,
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
     * Combines one application with its pre-projected release, manifest, and tenant-assignment views.
     *
     * @param list<ApplicationAdminReleaseViewDTO>          $releases
     * @param list<ApplicationAdminManifestViewDTO>         $manifests
     * @param list<ApplicationAdminTenantAssignmentViewDTO> $tenantAssignments
     */
    public static function fromApplication(ApplicationEntity $application, array $releases, array $manifests, array $tenantAssignments): self
    {
        return new self(
            id: $application->getId() ?? 0,
            nameEntity: $application->getName(),
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
