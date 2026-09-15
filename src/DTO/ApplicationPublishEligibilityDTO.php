<?php

declare(strict_types=1);

namespace App\Applicating\DTO;

final readonly class ApplicationPublishEligibilityDTO
{
    public function __construct(
        public int $releaseId,
        public bool $eligible,
        public ?string $reason,
    ) {
    }

    /** @return array{releaseId:int,eligible:bool,reason:string} */
    public function toReadinessArray(): array
    {
        return [
            'releaseId' => $this->releaseId,
            'eligible' => $this->eligible,
            'reason' => $this->reason ?? sprintf('Release %d is publish-eligible.', $this->releaseId),
        ];
    }

    /** @return array{eligible:bool,reason:string|null} */
    public function toLegacyMapItem(): array
    {
        return [
            'eligible' => $this->eligible,
            'reason' => $this->reason,
        ];
    }
}
