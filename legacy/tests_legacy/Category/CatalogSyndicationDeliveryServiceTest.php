<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Policy\CategorySyndicationDeliveryPolicy;
use App\Repository\CategorySyndicationDeliveryRecordRepository;
use App\Service\CatalogSyndicationDeliveryService;
use PHPUnit\Framework\TestCase;

final class CatalogSyndicationDeliveryServiceTest extends TestCase
{
    public function testRecordDeliveryReturnsLedgerPayload(): void
    {
        $service = new CatalogSyndicationDeliveryService(
            new CategorySyndicationDeliveryPolicy(),
            new CategorySyndicationDeliveryRecordRepository(),
        );

        $event = $service->recordDelivery(
            'delivery-100',
            'pkg-100',
            'dest-100',
            'category-500',
            'failed',
            1,
            503,
            'temporary downstream outage',
            'operator-1',
            'record delivery result',
        );

        $payload = $event->payload();
        self::assertSame('delivery-100', $payload['deliveryId']);
        self::assertSame('pkg-100', $payload['packageId']);
        self::assertSame('failed', $payload['status']);
        self::assertSame(503, $payload['responseCode']);
        self::assertTrue($payload['retryable']);
        self::assertNull($payload['deliveredAt']);
    }
}
