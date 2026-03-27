<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Policy\CategorySyndicationDeliveryPolicy;
use PHPUnit\Framework\TestCase;

final class CategorySyndicationDeliveryPolicyTest extends TestCase
{
    public function testAssertStatusRejectsUnsupportedStatus(): void
    {
        $policy = new CategorySyndicationDeliveryPolicy();

        $this->expectException(\InvalidArgumentException::class);
        $policy->assertStatus('unknown');
    }

    public function testAssertAttemptRejectsNonPositiveAttempt(): void
    {
        $policy = new CategorySyndicationDeliveryPolicy();

        $this->expectException(\InvalidArgumentException::class);
        $policy->assertAttempt(0);
    }
}
