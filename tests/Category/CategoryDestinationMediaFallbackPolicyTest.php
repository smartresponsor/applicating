<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Entity\CategoryMediaBinding;
use App\Policy\CategoryDestinationMediaFallbackPolicy;
use App\ValueObject\CategoryMediaRole;
use PHPUnit\Framework\TestCase;

final class CategoryDestinationMediaFallbackPolicyTest extends TestCase
{
    public function testBuildReportAllowsSharedFallbackWhenExactDestinationBindingIsMissing(): void
    {
        $policy = new CategoryDestinationMediaFallbackPolicy();
        $report = $policy->buildReport(
            'destination-1801',
            'category-1801',
            ['channel' => 'storefront', 'locale' => 'en_US', 'requiredMediaRoles' => ['primary']],
            [
                new CategoryMediaBinding('binding-shared', 'category-1801', 'asset-shared', CategoryMediaRole::primary(), [], [], true, true, [], 'operator-1', new \DateTimeImmutable()),
            ],
        );

        self::assertFalse($report->publishable());
        self::assertTrue($report->publishableWithFallback());
        self::assertTrue($report->checks()['globalSharedFallbackReady']);
        self::assertTrue($report->checks()['fallbackUsed']);
        self::assertSame(['binding-shared'], $report->fallbackMatchedBindingIds());
        self::assertSame([], $report->requiredMissing());
    }
}
