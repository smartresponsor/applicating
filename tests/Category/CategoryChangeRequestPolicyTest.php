<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Policy\CategoryChangeRequestPolicy;
use App\ValueObject\CategoryChangeRequestState;
use PHPUnit\Framework\TestCase;

final class CategoryChangeRequestPolicyTest extends TestCase
{
    public function testCanSubmitWithSummaryAndChanges(): void
    {
        $policy = new CategoryChangeRequestPolicy();

        self::assertTrue($policy->canSubmit(
            'req-100',
            'category-100',
            'author-1',
            'Adjust SEO title and move under winter',
            ['seoTitle' => 'Winter essentials', 'parentId' => 'winter'],
        ));
    }

    public function testCannotSubmitWithoutChanges(): void
    {
        $policy = new CategoryChangeRequestPolicy();

        self::assertFalse($policy->canSubmit(
            'req-101',
            'category-100',
            'author-1',
            'No actual payload',
            [],
        ));
    }

    public function testReviewRequiresReviewerAndDecisionReason(): void
    {
        $policy = new CategoryChangeRequestPolicy();

        self::assertFalse($policy->canReview(
            CategoryChangeRequestState::fromString(CategoryChangeRequestState::PROPOSED),
            CategoryChangeRequestState::fromString(CategoryChangeRequestState::ACCEPTED),
            'moderator-1',
            '   ',
        ));
    }
}
