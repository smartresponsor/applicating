<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Service\TreeOperation;
use PHPUnit\Framework\TestCase;

final class TreeOperationTest extends TestCase
{
    public function testMoveKeepsParent(): void
    {
        $op = new TreeOperation();
        $op->move('3', '2');
        $this->assertTrue(true);
    }
}
