<?php

declare(strict_types=1);

namespace App\Component\Product\Marketplace\DTO;

final class PluginManifest
{
    /** @param array<string,string> $meta @param array<int,string> $permissions */
    public function __construct(
        public string $name,
        public string $version,
        public string $author,
        public float $price_usd,
        public array $permissions = [],
        public array $meta = [],
    ) {
    }
}
