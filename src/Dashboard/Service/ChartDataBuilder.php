<?php

declare(strict_types=1);

namespace App\Component\Product\Dashboard\Service;

use App\Component\Product\Dashboard\DTO\ChartDTO;

final class ChartDataBuilder
{
    /** @param array<int,ChartDTO> $charts */
    public function toJson(array $charts): string
    {
        $out = [];
        foreach ($charts as $c) {
            $out[] = [
                'id' => $c->id,
                'title' => $c->title,
                'series' => $c->series,
            ];
        }

        return json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
