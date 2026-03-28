<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Policy\CategoryChangeRequestPolicy;
use App\Policy\CategoryPublicationGatePolicy;
use App\Policy\CategoryWorkflowPolicy;
use App\Repository\CategoryChangeRequestRepository;
use App\Repository\CategoryWorkflowRepository;
use App\Service\CatalogChangeRequestService;
use App\Service\CatalogPublicationGateService;
use App\Service\CatalogReviewDecisionCouplingService;
use App\Service\CatalogWorkflowTransitionService;
use PHPUnit\Framework\TestCase;

final class CatalogReviewDecisionCouplingServiceTest extends TestCase
{
    public function testAcceptedReviewTransitionsWorkflowAndEvaluatesReadiness(): void
    {
        $changeRequestRepository = new CategoryChangeRequestRepository();
        $workflowRepository = new CategoryWorkflowRepository();

        $changeRequestService = new CatalogChangeRequestService($changeRequestRepository, new CategoryChangeRequestPolicy());
        $workflowService = new CatalogWorkflowTransitionService($workflowRepository, new CategoryWorkflowPolicy());
        $publicationGateService = new CatalogPublicationGateService(new CategoryPublicationGatePolicy());

        $service = new CatalogReviewDecisionCouplingService(
            $changeRequestService,
            $workflowService,
            $publicationGateService,
        );

        $changeRequestService->submit(
            'req-510',
            'category-510',
            'author-1',
            'Promote category to spring landing',
            ['slug' => 'spring-landing'],
        );
        $changeRequestService->review('req-510', 'in_review', 'reviewer-0', 'Entered moderation');

        $event = $service->couple(
            'req-510',
            'accepted',
            'reviewer-1',
            'Approved for publish readiness',
            [
                'slugReady' => true,
                'seoReady' => true,
                'contentReady' => true,
                'localeReady' => true,
                'mediaReady' => false,
                'aliasReady' => true,
            ],
        );

        $payload = $event->payload();

        self::assertSame('accepted', $payload['reviewState']);
        self::assertSame('approved', $payload['workflowState']);
        self::assertTrue($payload['publishable']);
        self::assertSame([], $payload['blockers']);
        self::assertSame(['mediaReady'], $payload['warnings']);
    }

    public function testRejectedReviewReturnsCategoryToDraftAndMarksNotPublishable(): void
    {
        $changeRequestRepository = new CategoryChangeRequestRepository();
        $workflowRepository = new CategoryWorkflowRepository();

        $changeRequestService = new CatalogChangeRequestService($changeRequestRepository, new CategoryChangeRequestPolicy());
        $workflowService = new CatalogWorkflowTransitionService($workflowRepository, new CategoryWorkflowPolicy());
        $publicationGateService = new CatalogPublicationGateService(new CategoryPublicationGatePolicy());

        $service = new CatalogReviewDecisionCouplingService(
            $changeRequestService,
            $workflowService,
            $publicationGateService,
        );

        $changeRequestService->submit(
            'req-511',
            'category-511',
            'author-1',
            'Remove deprecated alias',
            ['alias' => 'old-path'],
        );
        $changeRequestService->review('req-511', 'in_review', 'reviewer-0', 'Entered moderation');

        $event = $service->couple(
            'req-511',
            'rejected',
            'reviewer-2',
            'Rejected due to incomplete content',
            [
                'slugReady' => false,
                'seoReady' => false,
                'contentReady' => false,
                'localeReady' => false,
            ],
        );

        $payload = $event->payload();

        self::assertSame('rejected', $payload['reviewState']);
        self::assertSame('draft', $payload['workflowState']);
        self::assertFalse($payload['publishable']);
        self::assertSame(['review_rejected'], $payload['blockers']);
        self::assertSame([], $payload['warnings']);
    }
}
