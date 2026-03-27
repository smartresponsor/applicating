<?php
declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 */
namespace App\Tools\Dx;

final class CategoryDx
{
    public static function main(array $argv): int
    {
        $cmd = $argv[1] ?? 'help';
        return match ($cmd) {
            'smoke' => self::smoke(),
            'fixtures' => self::fixtures(),
            default => self::help(),
        };
    }
    private static function smoke(): int
    {
        echo "SMOKE: /api/category/tree...\n";
        return 0;
    }
    private static function fixtures(): int
    {
        echo "FIXTURES: loading category fixtures...\n";
        return 0;
    }
    private static function help(): int
    {
        echo "Usage: php category-dx.php [smoke|fixtures]\n";
        return 0;
    }
}

CategoryDx::main($argv);
