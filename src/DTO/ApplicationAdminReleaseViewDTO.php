<?php

declare(strict_types=1);

namespace App\Applicating\DTO;

use App\Applicating\Entity\ApplicationReleaseEntity;

/**
 * Represents release identity, channel, download, and publication state on the administration detail surface.
 */
final readonly class ApplicationAdminReleaseViewDTO
{
    public function __construct(
        public int $id,
        public string $version,
        public string $channel,
        public string $downloadUrl,
        public string $publicationState,
    ) {
    }

    /**
     * Projects one persisted release into the administration detail view contract.
     */
    public static function fromRelease(ApplicationReleaseEntity $release): self
    {
        return new self(
            id: $release->getId() ?? 0,
            version: $release->getVersion(),
            channel: $release->getChannel(),
            downloadUrl: $release->getDownloadUrl(),
            publicationState: $release->getPublicationState()->value,
        );
    }
}
