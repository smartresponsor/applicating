<?php

declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 * Owner: Marketing America Corp
 */

namespace App\Tests\Category;

use App\Policy\CategorySyndicationGovernanceTrailPolicy;
use App\Service\CatalogSyndicationGovernanceTrailService;
use PHPUnit\Framework\TestCase;

final class CatalogSyndicationGovernanceTrailServiceTest extends TestCase
{
    public function testRecordTrailIncludesPolicyDeliveryAndHistorySignals(): void
    {
        $service = new CatalogSyndicationGovernanceTrailService(new CategorySyndicationGovernanceTrailPolicy());

        $event = $service->recordTrail(
            [
                'destinationId' => 'dst-1',
                'categoryId' => 'cat-1',
                'mediaPolicyMode' => 'prefer_exact_warn',
                'strictPublishable' => false,
                'fallbackPublishable' => true,
                'resolvedPublishable' => true,
                'fallbackUsed' => true,
                'warnings' => ['package_publishable_by_destination_media_policy_fallback'],
            ],
            [
                'destinationId' => 'dst-1',
                'categoryId' => 'cat-1',
                'status' => 'retry_scheduled',
                'retryable' => true,
            ],
            [
                'destinationId' => 'dst-1',
                'totalRecords' => 4,
                'deliveredCount' => 1,
                'failedCount' => 2,
                'pendingCount' => 0,
                'retryScheduledCount' => 1,
                'skippedCount' => 0,
            ],
            [
                'scheduledRetries' => 1,
            ],
            'actor-1',
            'test',
        );

        $payload = $event->payload();
        self::assertIsArray($payload['historyCounts'] ?? null);
        self::assertIsArray($payload['warnings'] ?? null);
        self::assertIsArray($payload['checks'] ?? null);
        /** @var array<string,int> $historyCounts */
        $historyCounts = $payload['historyCounts'];
        /** @var list<string> $warnings */
        $warnings = $payload['warnings'];
        /** @var array<string,bool> $checks */
        $checks = $payload['checks'];

        self::assertSame('prefer_exact_warn', $payload['mediaPolicyMode']);
        self::assertTrue($payload['resolvedPublishable']);
        self::assertTrue($payload['fallbackUsed']);
        self::assertTrue($payload['retryScheduled']);
        self::assertSame(2, $historyCounts['failedCount']);
        self::assertContains('governance_trail_fallback_used', $warnings);
        self::assertTrue($checks['governanceTrailHasFailures']);
    }
}
