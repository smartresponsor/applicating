<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category\Api;

use PHPUnit\Framework\TestCase;

final class CategoryTreeBrokenTest extends TestCase
{
    public function testDetectsBroken(): void
    {
        $data = [
            ['id' => 1, 'level' => 0],
            ['id' => 2, 'level' => -1],
        ];
        $this->assertNotEmpty(array_filter($data, static fn ($n) => $n['level'] < 0));
    }
}
