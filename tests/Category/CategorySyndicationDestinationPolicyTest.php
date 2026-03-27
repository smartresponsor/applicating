<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Policy\CategorySyndicationDestinationPolicy;
use PHPUnit\Framework\TestCase;

final class CategorySyndicationDestinationPolicyTest extends TestCase
{
    public function testNormalizeSettingsReturnsSortedTrimmedMap(): void
    {
        $policy = new CategorySyndicationDestinationPolicy();

        self::assertSame(
            [
                'channel' => 'web-us',
                'feedCode' => 'spring-catalog',
            ],
            $policy->normalizeSettings([
                ' feedCode ' => ' spring-catalog ',
                ' channel ' => ' web-us ',
            ]),
        );
    }
}
