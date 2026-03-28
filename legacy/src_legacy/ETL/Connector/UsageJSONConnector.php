<?php

declare(strict_types=1);

namespace App\Component\Product\ETL\Connector;

final class UsageJSONConnector
{
    /** @return array<int,array<string,mixed>> */
    public function parse(string $jsonFile): array
    {
        $raw = file_get_contents($jsonFile);
        $data = json_decode($raw ?: '[]', true);
        if (!is_array($data)) {
            return [];
        }

        return $data;
    }
}
