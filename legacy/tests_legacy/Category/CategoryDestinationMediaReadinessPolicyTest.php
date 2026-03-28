<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Policy\CategoryDestinationMediaReadinessPolicy;
use PHPUnit\Framework\TestCase;

final class CategoryDestinationMediaReadinessPolicyTest extends TestCase
{
    public function testBuildReportMarksMissingDestinationRoleAsHardRequirement(): void
    {
        $policy = new CategoryDestinationMediaReadinessPolicy();
        $report = $policy->buildReport(
            'dest-1',
            'category-1',
            ['channel' => 'storefront', 'locale' => 'en_US', 'requiredMediaRoles' => ['primary', 'hero']],
            ['channel' => 'storefront', 'locale' => 'en_US', 'requiredRoles' => ['primary', 'hero']],
            [
                'channelScopedMediaReady' => true,
                'localeScopedMediaReady' => true,
                'requiredRoleCoverageReady' => false,
                'exactChannelLocaleMatchReady' => true,
            ],
            ['destination_required_role:hero'],
            [],
            ['bind-1'],
        );

        self::assertFalse($report->publishable());
        self::assertContains('destination_required_role:hero', $report->requiredMissing());
        self::assertTrue($report->checks()['destinationChannelMediaReady']);
        self::assertFalse($report->checks()['destinationRequiredRolesReady']);
    }
}
