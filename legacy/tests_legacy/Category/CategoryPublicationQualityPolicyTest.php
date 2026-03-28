<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Policy\CategoryPublicationQualityPolicy;
use PHPUnit\Framework\TestCase;

final class CategoryPublicationQualityPolicyTest extends TestCase
{
    public function testBuildProfileReturnsReadyRiskForHealthyCategory(): void
    {
        $policy = new CategoryPublicationQualityPolicy();

        $profile = $policy->buildProfile(100, [
            'slugReady' => true,
            'seoReady' => true,
            'contentReady' => true,
            'localeReady' => true,
            'mediaReady' => true,
            'aliasReady' => true,
        ], [
            'bannerReady' => true,
            'htmlBlockReady' => true,
        ]);

        self::assertTrue($profile->isPublishableQuality());
        self::assertSame('ready', $profile->riskLevel());
        self::assertSame([], $profile->hardBlockers());
        self::assertSame([], $profile->softWarnings());
        self::assertSame([], $profile->advisoryWarnings());
    }

    public function testBuildProfileReturnsCriticalRiskForMissingRequiredChecks(): void
    {
        $policy = new CategoryPublicationQualityPolicy();

        $profile = $policy->buildProfile(44, [
            'slugReady' => true,
            'seoReady' => false,
            'contentReady' => true,
            'localeReady' => false,
            'mediaReady' => false,
            'aliasReady' => false,
        ], [
            'bannerReady' => false,
            'htmlBlockReady' => false,
        ]);

        self::assertFalse($profile->isPublishableQuality());
        self::assertSame('critical', $profile->riskLevel());
        self::assertContains('seoReady', $profile->hardBlockers());
        self::assertContains('localeReady', $profile->hardBlockers());
        self::assertContains('qualityScoreCritical', $profile->hardBlockers());
        self::assertContains('mediaReady', $profile->softWarnings());
        self::assertContains('bannerReady', $profile->advisoryWarnings());
    }
}
