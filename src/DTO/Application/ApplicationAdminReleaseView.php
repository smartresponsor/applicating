<?php

declare(strict_types=1);

namespace App\DTO\Application;

use App\Entity\ApplicationRelease;

final class ApplicationAdminReleaseView
{
    public function __construct(
        public readonly int $id,
        public readonly string $version,
        public readonly string $channel,
        public readonly string $downloadUrl,
        public readonly string $publicationState,
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
