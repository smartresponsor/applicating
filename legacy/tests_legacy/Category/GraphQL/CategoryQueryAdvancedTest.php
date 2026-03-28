<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category\GraphQL;

use App\GraphQl\CategoryQuery;
use PHPUnit\Framework\TestCase;

final class CategoryQueryAdvancedTest extends TestCase
{
    public function testLocaleFilter(): void
    {
        $q = new CategoryQuery();
        $res = $q(null, ['locale' => 'en', 'first' => 5], null, null);
        $this->assertNotEmpty($res);
        foreach ($res as $row) {
            $this->assertSame('en', $row['locale']);
        }
    }
}
