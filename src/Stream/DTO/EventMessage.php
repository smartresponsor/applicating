<?php

declare(strict_types=1);

namespace App\Component\Product\Stream\DTO;

final class EventMessage
{
    /** @param array<string,mixed> $payload */
    public function __construct(
        public string $type,
        public string $tenantId,
        public array $payload,
        public string $ts,
    ) {
    }
}
