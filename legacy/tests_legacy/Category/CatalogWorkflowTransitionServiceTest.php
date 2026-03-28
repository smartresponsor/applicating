<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Policy\CategoryWorkflowPolicy;
use App\Repository\CategoryWorkflowRepository;
use App\Service\CatalogWorkflowTransitionService;
use App\ValueObject\CategoryWorkflowState;
use PHPUnit\Framework\TestCase;

final class CatalogWorkflowTransitionServiceTest extends TestCase
{
    public function testTransitionStoresCurrentStateAndAppendsAuditEvent(): void
    {
        $repository = new CategoryWorkflowRepository();
        $service = new CatalogWorkflowTransitionService($repository, new CategoryWorkflowPolicy());

        $event = $service->transition(
            'category-100',
            CategoryWorkflowState::IN_REVIEW,
            'operator-1',
            'ready for moderation',
        );

        $current = $repository->findByCategoryId('category-100');
        self::assertNotNull($current);
        self::assertSame(CategoryWorkflowState::IN_REVIEW, $current->state()->value());
        self::assertSame('operator-1', $current->actorId());
        self::assertSame('ready for moderation', $current->reason());

        $payload = $event->payload();
        self::assertSame('draft', $payload['fromState']);
        self::assertSame('in_review', $payload['toState']);

        $history = $repository->historyForCategoryId('category-100');
        self::assertCount(1, $history);
        self::assertSame('category-100', $history[0]->payload()['categoryId']);
    }

    public function testTransitionRejectsInvalidStateJump(): void
    {
        $repository = new CategoryWorkflowRepository();
        $service = new CatalogWorkflowTransitionService($repository, new CategoryWorkflowPolicy());

        $this->expectException(\DomainException::class);

        $service->transition(
            'category-101',
            CategoryWorkflowState::PUBLISHED,
            'operator-1',
            'skipping review',
        );
    }
}
