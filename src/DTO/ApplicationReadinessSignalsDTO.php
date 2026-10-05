<?php

declare(strict_types=1);

namespace App\Applicating\DTO;

/**
 * Carries the diagnostic signals used to explain an application's publication readiness.
 */
final readonly class ApplicationReadinessSignalsDTO
{
    /** @param array<int, array{releaseId:int,eligible:bool,reason:string}> $eligibility */
    public function __construct(
        public bool $applicationFound,
        public string $applicationSlug,
        public int $releaseCount,
        public int $manifestCount,
        public bool $approvedManifestPresent,
        public array $eligibility,
    ) {
    }

    /**
     * Returns the stable JSON-ready readiness-signal shape.
     *
     * @return array{applicationFound:bool,applicationSlug:string,releaseCount:int,manifestCount:int,approvedManifestPresent:bool,eligibility:array<int, array{releaseId:int,eligible:bool,reason:string}>}
     */
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
