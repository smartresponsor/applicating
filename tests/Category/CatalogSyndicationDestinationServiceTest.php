<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Policy\CategorySyndicationDestinationPolicy;
use App\Repository\CategorySyndicationDestinationRepository;
use App\Service\CatalogSyndicationDestinationService;
use PHPUnit\Framework\TestCase;

final class CatalogSyndicationDestinationServiceTest extends TestCase
{
    public function testRegisterBuildsDestinationEventPayload(): void
    {
        $service = new CatalogSyndicationDestinationService(
            new CategorySyndicationDestinationPolicy(),
            new CategorySyndicationDestinationRepository(),
        );

        $event = $service->register(
            'destination-01',
            'US Search Feed',
            'search',
            'export',
            true,
            [
                'feedCode' => 'search-us',
                'channel' => 'web-us',
            ],
            'operator-7',
            'baseline syndication destination registration',
        );

        $payload = $event->payload();
        self::assertSame('destination-01', $payload['destinationId']);
        self::assertSame('search', $payload['destinationType']);
        self::assertSame('export', $payload['deliveryMode']);
        self::assertTrue($payload['enabled']);
        self::assertSame('operator-7', $payload['actorId']);
        self::assertSame('baseline syndication destination registration', $payload['reason']);
    }
}
