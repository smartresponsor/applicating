<?php

declare(strict_types=1);

namespace App\Applicating\DTO;

/**
 * Carries the publish-eligibility decision and explanatory reason for one release.
 */
final readonly class ApplicationPublishEligibilityDTO
{
    public function __construct(
        public int $releaseId,
        public bool $eligible,
        public ?string $reason,
    ) {
    }

    /**
     * Returns the readiness-facing eligibility shape, synthesizing a positive reason when none is stored.
     *
     * @return array{releaseId:int,eligible:bool,reason:string}
     */
    public function toReadinessArray(): array
    {
        return [
            'releaseId' => $this->releaseId,
            'eligible' => $this->eligible,
            'reason' => $this->reason ?? sprintf('Release %d is publish-eligible.', $this->releaseId),
        ];
    }

    /**
     * Returns the legacy eligibility-map item used by the existing administration detail payload.
     *
     * @return array{eligible:bool,reason:string|null}
     */
    public function toLegacyMapItem(): array
    {
        return [
            'eligible' => $this->eligible,
            'reason' => $this->reason,
        ];
    }
}
