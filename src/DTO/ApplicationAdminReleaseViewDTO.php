<?php

declare(strict_types=1);

namespace App\Applicating\DTO;

use App\Applicating\Entity\ApplicationReleaseEntity;

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
