<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Policy\CategoryCompletenessPolicy;
use App\Policy\CategoryPublicationGatePolicy;
use App\Service\CatalogCompletenessService;
use App\Service\CatalogPublicationGateService;
use App\ValueObject\CategoryWorkflowState;
use PHPUnit\Framework\TestCase;

final class CategoryPublicationCompletenessBridgeTest extends TestCase
{
    public function testCompletenessPublicationChecksCanDrivePublicationGateEvaluation(): void
    {
        $completeness = new CatalogCompletenessService(new CategoryCompletenessPolicy());
        $gate = new CatalogPublicationGateService(new CategoryPublicationGatePolicy());

        $completenessEvent = $completeness->evaluate('category-603', [
            'slug' => 'chairs',
            'seo' => [
                'title' => 'Chairs',
                'description' => 'Browse chairs',
            ],
            'content' => [
                'body' => 'Curated category copy',
            ],
            'locale' => [
                'enabled' => ['en_US', 'uk_UA'],
            ],
            'media' => [
                'primaryAssetId' => '',
            ],
            'aliases' => [],
            'presentation' => [
                'bannerId' => '',
                'htmlBlockId' => '',
            ],
        ], 'operator-3', 'approval handoff');

        /** @var array{publicationChecks:array<string,bool>} $completenessPayload */
        $completenessPayload = $completenessEvent->payload();
        $gateEvent = $gate->evaluate(
            'category-603',
            CategoryWorkflowState::APPROVED,
            $completenessPayload['publicationChecks'],
            'operator-3',
            'approval handoff',
        );

        /** @var array{publishable:bool,warnings:list<string>} $payload */
        $payload = $gateEvent->payload();
        self::assertTrue($payload['publishable']);
        self::assertSame(['mediaReady', 'aliasReady'], $payload['warnings']);
    }
}
