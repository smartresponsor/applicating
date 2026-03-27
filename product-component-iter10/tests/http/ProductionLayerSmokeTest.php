<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ProductionLayerSmokeTest extends TestCase
{
    public function testOpenApiExists(): void
    {
        $this->assertTrue(file_exists(__DIR__ . '/../../openapi/base.yaml'));
    }
}
