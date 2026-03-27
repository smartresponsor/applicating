<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Tools;

use PHPUnit\Framework\TestCase;

final class CategoryPostgresMatrixSmokeScriptTest extends TestCase
{
    public function testSmokeScriptGracefullySkipsWhenDsnVariablesAreNotProvided(): void
    {
        $script = dirname(__DIR__, 2).'/tools/smoke/category-postgres-matrix-smoke.php';
        $command = escapeshellarg(PHP_BINARY)
            .' -r '
            .escapeshellarg(
                'putenv(\'CATEGORY_TEST_LOCAL_DATABASE_URL\');'
                .'putenv(\'CATEGORY_TEST_DOCKER_DATABASE_URL\');'
                .'require '.var_export($script, true).';'
            );

        exec($command, $output, $exitCode);
        $text = implode("\n", $output);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('No DSNs provided. Nothing executed.', $text);
    }
}
