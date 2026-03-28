<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Service\CatalogCacheService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class CatalogCacheServiceTest extends TestCase
{
    public function testGetTreeCaches(): void
    {
        $cache = new ArrayAdapter();
        $svc = new CatalogCacheService($cache);
        $first = $svc->getTree('en');
        $second = $svc->getTree('en');
        self::assertSame($first, $second);
    }
}
