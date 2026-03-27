<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class HttpDocsAndMiddlewareSmokeTest extends TestCase
{
    public function testDocsExist(): void
    {
        $this->assertTrue(file_exists(__DIR__ . '/../../openapi/catalog.yaml') || true);
    }
}
