<?php

declare(strict_types=1);

namespace App\Application\DTO\Application;

use App\Application\Entity\ApplicationRelease;

final readonly class ApplicationAdminReleaseView
{
    public function __construct(
        public int $id,
        public string $version,
        public string $channel,
        public string $downloadUrl,
        public string $publicationState,
    ) {
    }

    public static function fromRelease(ApplicationRelease $release): self
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
