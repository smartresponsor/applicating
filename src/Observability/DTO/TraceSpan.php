<?php

declare(strict_types=1);

namespace App\Component\Product\Observability\DTO;

final class TraceSpan
{
    public function __construct(
        public string $trace_id,
        public string $span_id,
        public string $parent_id,
        public string $name,
        public string $start_ts,
        public int $duration_ms,
        /** @var array<string,string> */
        public array $attributes = [],
    ) {
    }
}
