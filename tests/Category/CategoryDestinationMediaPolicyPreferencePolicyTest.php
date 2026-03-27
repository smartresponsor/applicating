<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Policy\CategoryDestinationMediaPolicyPreferencePolicy;
use PHPUnit\Framework\TestCase;

final class CategoryDestinationMediaPolicyPreferencePolicyTest extends TestCase
{
    public function testStrictExactRejectsFallbackOnlyCoverage(): void
    {
        $policy = new CategoryDestinationMediaPolicyPreferencePolicy();
        $report = $policy->buildReport(
            'strict_exact',
            ['publishable' => false, 'requiredMissing' => ['destination_required_role:hero'], 'warnings' => [], 'checks' => []],
            ['publishableWithFallback' => true, 'requiredMissing' => [], 'warnings' => ['fallback_used_for_role:hero'], 'checks' => ['fallbackUsed' => true]],
        );

        self::assertFalse($report->resolvedPublishable());
        self::assertTrue($report->fallbackPublishable());
        self::assertSame(['destination_required_role:hero'], $report->requiredMissing());
        self::assertContains('destination_media_policy_strict_exact_rejected_fallback', $report->warnings());
    }

    public function testPreferExactWarnAllowsFallbackButEmitsWarning(): void
    {
        $policy = new CategoryDestinationMediaPolicyPreferencePolicy();
        $report = $policy->buildReport(
            'prefer_exact_warn',
            ['publishable' => false, 'requiredMissing' => ['destination_required_role:primary'], 'warnings' => [], 'checks' => []],
            ['publishableWithFallback' => true, 'requiredMissing' => [], 'warnings' => ['fallback_used_for_role:primary'], 'checks' => ['fallbackUsed' => true]],
        );

        self::assertTrue($report->resolvedPublishable());
        self::assertTrue($report->checks()['fallbackAcceptedByPolicy']);
        self::assertContains('destination_media_policy_preferred_exact_fallback_used', $report->warnings());
    }
}
