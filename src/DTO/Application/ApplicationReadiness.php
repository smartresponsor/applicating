<?php

declare(strict_types=1);

namespace App\Application\DTO\Application;

final readonly class ApplicationReadiness
{
    /**
     * @param list<string> $blockingReasons
     * @param list<string> $warnings
     */
    public function __construct(
        public bool $canPublish,
        public array $blockingReasons,
        public array $warnings,
        public ApplicationReadinessSignals $signals,
    ) {
    }

    /** @return array{canPublish:bool,blockingReasons:list<string>,warnings:list<string>,signals:array{applicationFound:bool,applicationSlug:string,releaseCount:int,manifestCount:int,approvedManifestPresent:bool,eligibility:array<int, array{releaseId:int,eligible:bool,reason:string}>}} */
    public function toArray(): array
    {
        return [
            'canPublish' => $this->canPublish,
            'blockingReasons' => $this->blockingReasons,
            'warnings' => $this->warnings,
            'signals' => $this->signals->toArray(),
        ];
    }
}
