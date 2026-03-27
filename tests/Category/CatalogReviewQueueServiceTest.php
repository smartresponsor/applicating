<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Policy\CategoryChangeRequestPolicy;
use App\Policy\CategoryReviewAssignmentPolicy;
use App\Repository\CategoryChangeRequestRepository;
use App\Repository\CategoryReviewAssignmentRepository;
use App\Service\CatalogChangeRequestService;
use App\Service\CatalogReviewAssignmentService;
use App\Service\CatalogReviewQueueService;
use PHPUnit\Framework\TestCase;

final class CatalogReviewQueueServiceTest extends TestCase
{
    public function testBuildsReadyQueueItemForAssignedReviewer(): void
    {
        $changeRepository = new CategoryChangeRequestRepository();
        $assignmentRepository = new CategoryReviewAssignmentRepository();
        $requestService = new CatalogChangeRequestService($changeRepository, new CategoryChangeRequestPolicy());
        $assignmentService = new CatalogReviewAssignmentService(
            $changeRepository,
            $assignmentRepository,
            new CategoryReviewAssignmentPolicy(),
        );
        $queueService = new CatalogReviewQueueService($changeRepository, $assignmentRepository);

        $requestService->submit(
            'req-410',
            'category-410',
            'author-1',
            'Adjust category placement',
            ['parentId' => 'garden'],
        );
        $requestService->review('req-410', 'in_review', 'moderator-1', 'Opened for review');
        $assignmentService->assign('req-410', 'reviewer-1', 'lead-1', 'urgent');

        $queue = $queueService->queueForReviewer('reviewer-1');

        self::assertCount(1, $queue);
        self::assertSame('req-410', $queue[0]->requestId());
        self::assertTrue($queue[0]->readyForReview());
        self::assertSame('urgent', $queue[0]->priority());
        self::assertSame([], $queue[0]->readinessWarnings());
    }

    public function testFlagsNotStartedRequestAsNotReady(): void
    {
        $changeRepository = new CategoryChangeRequestRepository();
        $assignmentRepository = new CategoryReviewAssignmentRepository();
        $requestService = new CatalogChangeRequestService($changeRepository, new CategoryChangeRequestPolicy());
        $assignmentService = new CatalogReviewAssignmentService(
            $changeRepository,
            $assignmentRepository,
            new CategoryReviewAssignmentPolicy(),
        );
        $queueService = new CatalogReviewQueueService($changeRepository, $assignmentRepository);

        $requestService->submit(
            'req-411',
            'category-411',
            'author-1',
            'Update seasonal sorting',
            ['sort' => 'seasonal'],
        );
        $assignmentService->assign('req-411', 'reviewer-1', 'lead-1', 'normal');

        $queue = $queueService->queueForReviewer('reviewer-1');

        self::assertCount(1, $queue);
        self::assertFalse($queue[0]->readyForReview());
        self::assertContains('request_not_started', $queue[0]->readinessWarnings());
    }
}
