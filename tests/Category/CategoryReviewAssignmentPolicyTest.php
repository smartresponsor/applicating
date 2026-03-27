<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Entity\CategoryChangeRequest;
use App\Policy\CategoryReviewAssignmentPolicy;
use App\ValueObject\CategoryChangeRequestState;
use PHPUnit\Framework\TestCase;

final class CategoryReviewAssignmentPolicyTest extends TestCase
{
    public function testAllowsAssignmentForProposedRequestWithValidPriority(): void
    {
        $policy = new CategoryReviewAssignmentPolicy();
        $request = CategoryChangeRequest::open('req-400', 'category-400', 'author-1', 'Update seo copy', ['metaTitle' => 'Garden']);

        self::assertTrue($policy->canAssign($request, 'reviewer-1', 'lead-1', 'high'));
    }

    public function testRejectsAssignmentForClosedRequestState(): void
    {
        $policy = new CategoryReviewAssignmentPolicy();
        $request = CategoryChangeRequest::open('req-401', 'category-401', 'author-1', 'Archive old alias', ['alias' => 'old'])
            ->moveTo(CategoryChangeRequestState::accepted(), 'moderator-1', 'Looks good');

        self::assertFalse($policy->canAssign($request, 'reviewer-1', 'lead-1', 'normal'));
    }
}
