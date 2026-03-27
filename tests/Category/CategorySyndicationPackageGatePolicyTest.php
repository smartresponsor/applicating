<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Policy\CategorySyndicationPackageGatePolicy;
use PHPUnit\Framework\TestCase;

final class CategorySyndicationPackageGatePolicyTest extends TestCase
{
    public function testBuildReportBlocksPublishWhenPackageOrMediaMissing(): void
    {
        $policy = new CategorySyndicationPackageGatePolicy();

        $report = $policy->buildReport(
            ['seo_title'],
            ['destination_required_role:hero'],
            ['destination_exact_match_missing'],
            ['destinationMediaPublishable' => false],
            ['bind-1'],
        );

        self::assertFalse($report->publishable());
        self::assertFalse($report->checks()['packageGatePublishable']);
        self::assertSame(['seo_title'], $report->packageMissingRequiredFields());
        self::assertSame(['destination_required_role:hero'], $report->mediaRequiredMissing());
    }
}
