<?php

declare(strict_types=1);

namespace App\DTO\Application;

final class ApplicationReadiness
{
    /**
     * @param list<string> $blockingReasons
     * @param list<string> $warnings
     * @param array<string, mixed> $signals
     */
    public function __construct(
        public readonly bool $canPublish,
        public readonly array $blockingReasons,
        public readonly array $warnings,
        public readonly array $signals,
    ) {
    }
}
