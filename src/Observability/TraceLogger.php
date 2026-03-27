<?php

declare(strict_types=1);

namespace App\Component\Product\Observability;

use App\Component\Product\Observability\DTO\TraceSpan;
use Doctrine\DBAL\Connection;

final class TraceLogger
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function write(TraceSpan $s): void
    {
        $this->db->insert('traces', [
            'trace_id' => $s->trace_id,
            'span_id' => $s->span_id,
            'parent_id' => $s->parent_id,
            'name' => $s->name,
            'start_ts' => $s->start_ts,
            'duration_ms' => $s->duration_ms,
            'attributes' => json_encode($s->attributes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }
}
