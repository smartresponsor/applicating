<?php

declare(strict_types=1);

namespace App\Component\Product\Observability\Exporter;

final class OtelExporter
{
    /** @param array<int,array<string,mixed>> $spans */
    public function toJson(array $spans): string
    {
        return json_encode(['resourceSpans' => [
            ['scopeSpans' => [['spans' => $spans]]],
        ]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
