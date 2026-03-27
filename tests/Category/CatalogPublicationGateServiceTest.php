<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Policy\CategoryPublicationGatePolicy;
use App\Service\CatalogPublicationGateService;
use App\ValueObject\CategoryWorkflowState;
use PHPUnit\Framework\TestCase;

final class CatalogPublicationGateServiceTest extends TestCase
{
    public function testEvaluateReturnsPublishablePayloadForApprovedReadyCategory(): void
    {
        $service = new CatalogPublicationGateService(new CategoryPublicationGatePolicy());

        $event = $service->evaluate(
            'category-200',
            CategoryWorkflowState::APPROVED,
            [
                'slugReady' => true,
                'seoReady' => true,
                'contentReady' => true,
                'localeReady' => true,
                'mediaReady' => false,
                'aliasReady' => false,
            ],
            'operator-1',
            'release candidate approved',
        );

        $payload = $event->payload();
        self::assertTrue($payload['publishable']);
        self::assertSame([], $payload['blockers']);
        self::assertSame(['mediaReady', 'aliasReady'], $payload['warnings']);
        self::assertSame('approved', $payload['workflowState']);
    }

    public function testAssertCanPublishFailsForDraftState(): void
    {
        $service = new CatalogPublicationGateService(new CategoryPublicationGatePolicy());

        $this->expectException(\DomainException::class);

        $service->assertCanPublish(
            CategoryWorkflowState::DRAFT,
            [
                'slugReady' => true,
                'seoReady' => true,
                'contentReady' => true,
                'localeReady' => true,
            ],
            'operator-1',
            'attempting premature publish',
        );
    }

    public function testAssertCanPublishFailsForMissingRequiredChecks(): void
    {
        $service = new CatalogPublicationGateService(new CategoryPublicationGatePolicy());

        $this->expectException(\DomainException::class);

        $service->assertCanPublish(
            CategoryWorkflowState::APPROVED,
            [
                'slugReady' => true,
                'seoReady' => false,
                'contentReady' => true,
                'localeReady' => true,
            ],
            'operator-1',
            'seo still incomplete',
        );
    }
}
