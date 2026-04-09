<?php

declare(strict_types=1);

namespace App\DTO\Application;

final class ApplicationReadinessSignals
{
    /** @param array<int, array{releaseId:int,eligible:bool,reason:string}> $eligibility */
    public function __construct(
        public readonly bool $applicationFound,
        public readonly string $applicationSlug,
        public readonly int $releaseCount,
        public readonly int $manifestCount,
        public readonly bool $approvedManifestPresent,
        public readonly array $eligibility,
    ) {
    }

    /** @return array{applicationFound:bool,applicationSlug:string,releaseCount:int,manifestCount:int,approvedManifestPresent:bool,eligibility:array<int, array{releaseId:int,eligible:bool,reason:string}>} */
    public function toArray(): array
    {
        return [
            'applicationFound' => $this->applicationFound,
            'applicationSlug' => $this->applicationSlug,
            'releaseCount' => $this->releaseCount,
            'manifestCount' => $this->manifestCount,
            'approvedManifestPresent' => $this->approvedManifestPresent,
            'eligibility' => $this->eligibility,
        ];
    }
}
