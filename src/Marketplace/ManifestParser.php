<?php

declare(strict_types=1);

namespace App\Component\Product\Marketplace;

use App\Component\Product\Marketplace\DTO\PluginManifest;

final class ManifestParser
{
    public function parse(string $json): PluginManifest
    {
        $d = json_decode($json, true) ?: [];

        return new PluginManifest(
            $d['name'] ?? 'plugin',
            $d['version'] ?? '0.0.1',
            $d['author'] ?? 'unknown',
            (float) ($d['price_usd'] ?? 0.0),
            $d['permissions'] ?? [],
            $d['meta'] ?? []
        );
    }
}
