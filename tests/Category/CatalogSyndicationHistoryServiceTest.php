<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Entity\CategorySyndicationDeliveryRecord;
use App\Policy\CategorySyndicationHistoryPolicy;
use App\Policy\CategorySyndicationRetryPolicy;
use App\Service\CatalogSyndicationHistoryService;
use App\ValueObject\CategorySyndicationDeliveryStatus;
use PHPUnit\Framework\TestCase;

final class CatalogSyndicationHistoryServiceTest extends TestCase
{
    public function testBuildDestinationHistoryReturnsStatusBreakdown(): void
    {
        $service = new CatalogSyndicationHistoryService(
            new CategorySyndicationHistoryPolicy(),
            new CategorySyndicationRetryPolicy(),
        );

        $records = [
            new CategorySyndicationDeliveryRecord('d1', 'p1', 'dest-1', 'c1', new CategorySyndicationDeliveryStatus('delivered'), 1, 200, 'ok', new \DateTimeImmutable('2025-01-01T00:00:00+00:00')),
            new CategorySyndicationDeliveryRecord('d2', 'p2', 'dest-1', 'c2', new CategorySyndicationDeliveryStatus('failed'), 1, 503, 'temporary outage', null),
            new CategorySyndicationDeliveryRecord('d3', 'p2', 'dest-1', 'c2', new CategorySyndicationDeliveryStatus('retry_scheduled'), 2, null, 'retry planned', null),
            new CategorySyndicationDeliveryRecord('d4', 'p9', 'dest-2', 'c9', new CategorySyndicationDeliveryStatus('delivered'), 1, 200, 'other destination', new \DateTimeImmutable('2025-01-02T00:00:00+00:00')),
        ];

        $event = $service->buildDestinationHistory('dest-1', $records, 'operator-1', 'build history');
        $payload = $event->payload();

        self::assertSame('dest-1', $payload['destinationId']);
        self::assertSame(3, $payload['totalRecords']);
        self::assertSame(1, $payload['deliveredCount']);
        self::assertSame(1, $payload['failedCount']);
        self::assertSame(1, $payload['retryScheduledCount']);
        self::assertSame(2, $payload['maxAttempt']);
        self::assertSame(['p1', 'p2'], $payload['packageIds']);
    }

    public function testConsolidateRecoveryAuditReturnsRetryAndRecoverySummary(): void
    {
        $service = new CatalogSyndicationHistoryService(
            new CategorySyndicationHistoryPolicy(),
            new CategorySyndicationRetryPolicy(),
        );

        $records = [
            new CategorySyndicationDeliveryRecord('d1', 'p1', 'dest-1', 'c1', new CategorySyndicationDeliveryStatus('failed'), 1, 503, 'temporary outage', null),
            new CategorySyndicationDeliveryRecord('d2', 'p1', 'dest-1', 'c1', new CategorySyndicationDeliveryStatus('retry_scheduled'), 2, null, 'retry planned', null),
            new CategorySyndicationDeliveryRecord('d3', 'p1', 'dest-1', 'c1', new CategorySyndicationDeliveryStatus('delivered'), 2, 200, 'recovered', new \DateTimeImmutable('2025-01-03T00:00:00+00:00')),
            new CategorySyndicationDeliveryRecord('d4', 'p2', 'dest-1', 'c2', new CategorySyndicationDeliveryStatus('failed'), 1, 400, 'bad request', null),
        ];

        $event = $service->consolidateRecoveryAudit('dest-1', $records, 'operator-1', 'consolidate recovery audit');
        $payload = $event->payload();

        self::assertSame(2, $payload['totalFailed']);
        self::assertSame(1, $payload['retryableFailed']);
        self::assertSame(1, $payload['nonRetryableFailed']);
        self::assertSame(1, $payload['scheduledRetries']);
        self::assertSame(1, $payload['deliveredAfterRetry']);
        self::assertSame(2, $payload['maxAttemptSeen']);
        self::assertSame(['c1', 'c2'], $payload['affectedCategoryIds']);
    }
}
