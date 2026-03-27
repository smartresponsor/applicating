<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Policy\CategorySyndicationRetryPolicy;
use PHPUnit\Framework\TestCase;

final class CategorySyndicationRetryPolicyTest extends TestCase
{
    public function testRetryableClassificationMatchesExpectedCodes(): void
    {
        $policy = new CategorySyndicationRetryPolicy();

        self::assertTrue($policy->isRetryable(null));
        self::assertTrue($policy->isRetryable(429));
        self::assertTrue($policy->isRetryable(503));
        self::assertFalse($policy->isRetryable(400));
    }

    public function testDelayScheduleMatchesExpectedSteps(): void
    {
        $policy = new CategorySyndicationRetryPolicy();

        self::assertSame(300, $policy->delaySecondsForAttempt(1));
        self::assertSame(900, $policy->delaySecondsForAttempt(2));
        self::assertSame(1800, $policy->delaySecondsForAttempt(3));
        self::assertSame(3600, $policy->delaySecondsForAttempt(4));
    }
}
