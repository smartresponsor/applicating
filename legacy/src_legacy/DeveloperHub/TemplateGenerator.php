<?php

declare(strict_types=1);

namespace App\Component\Product\DeveloperHub;

final class TemplateGenerator
{
    /** @return array{plugin_json:string, readme_md:string} */
    public function make(string $name, string $author, float $price = 0.0): array
    {
        $manifest = [
            'name' => $name,
            'version' => '0.1.0',
            'author' => $author,
            'price_usd' => $price,
            'permissions' => ['read:forecast'],
            'meta' => ['compat' => '>=23.0'],
        ];
        $readme = '# ' + $name + "\n\nПлагин для Smartresponsor.\n";

        return ['plugin_json' => json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'readme_md' => $readme];
    }
}
