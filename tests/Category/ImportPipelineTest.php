<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Category;

use App\Service\ImportPipeline;
use PHPUnit\Framework\TestCase;

final class ImportPipelineTest extends TestCase
{
    public function testProcessResultMarksFailureWhenSlugMissing(): void
    {
        $dir = sys_get_temp_dir().'/import-pipeline-test-'.bin2hex(random_bytes(4));
        mkdir($dir, 0777, true);

        $pipeline = new ImportPipeline($dir);
        $result = $pipeline->processResult(['locale' => 'en']);

        self::assertSame('failed', $result['status']);
        self::assertSame('Import item slug is required', $result['reason']);
        self::assertFileExists($dir.'/dlq.ndjson');

        @unlink($dir.'/dlq.ndjson');
        @rmdir($dir);
    }

    public function testProcessResultIncludesDlqFailureDetailsWhenPathMissing(): void
    {
        $dir = sys_get_temp_dir().'/import-pipeline-test-missing-'.bin2hex(random_bytes(4));
        $pipeline = new ImportPipeline($dir);

        $result = $pipeline->processResult(['locale' => 'en']);

        self::assertSame('failed', $result['status']);
        self::assertStringContainsString('Import item slug is required', (string) $result['reason']);
        self::assertStringContainsString('DLQ write failed: DLQ path does not exist:', (string) $result['reason']);
    }
}
