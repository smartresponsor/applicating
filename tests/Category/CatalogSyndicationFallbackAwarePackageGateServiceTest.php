<?php

declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 * Owner: Marketing America Corp
 */

namespace App\Tests\Category;

use App\Entity\CategoryMediaBinding;
use App\Entity\CategorySyndicationDestination;
use App\Policy\CategoryDestinationMediaFallbackPolicy;
use App\Policy\CategoryDestinationMediaReadinessPolicy;
use App\Policy\CategoryMediaApplicabilityPolicy;
use App\Policy\CategorySyndicationFallbackAwarePackageGatePolicy;
use App\Policy\CategorySyndicationMappingPolicy;
use App\Repository\CategoryMediaBindingRepository;
use App\Repository\CategorySyndicationDestinationRepository;
use App\Service\CatalogDestinationMediaFallbackService;
use App\Service\CatalogDestinationMediaReadinessService;
use App\Service\CatalogMediaApplicabilityService;
use App\Service\CatalogSyndicationFallbackAwarePackageGateService;
use App\Service\CatalogSyndicationMappingService;
use App\ValueObject\CategoryMediaRole;
use PHPUnit\Framework\TestCase;

final class CatalogSyndicationFallbackAwarePackageGateServiceTest extends TestCase
{
    public function testDistinguishesStrictAndFallbackPublishability(): void
    {
        $destinationRepository = new CategorySyndicationDestinationRepository();
        $destinationRepository->save(CategorySyndicationDestination::register(
            'dest-1',
            'Search Export',
            'search',
            'export',
            true,
            [
                'channel' => 'web',
                'locale' => 'en_US',
                'requiredMediaRoles' => '["banner"]',
            ],
            'operator-1',
        ));

        $bindingRepository = new CategoryMediaBindingRepository();
        $bindingRepository->save(new CategoryMediaBinding(
            'bind-1',
            'cat-1',
            'asset-1',
            CategoryMediaRole::banner(),
            [],
            ['en_US'],
            true,
            true,
            [],
            'operator-1',
            new \DateTimeImmutable('now'),
        ));

        $mappingService = new CatalogSyndicationMappingService(new CategorySyndicationMappingPolicy());
        $readinessService = new CatalogDestinationMediaReadinessService(
            $destinationRepository,
            new CatalogMediaApplicabilityService($bindingRepository, new CategoryMediaApplicabilityPolicy()),
            new CategoryDestinationMediaReadinessPolicy(),
        );
        $fallbackService = new CatalogDestinationMediaFallbackService($destinationRepository, $bindingRepository, new CategoryDestinationMediaFallbackPolicy());
        $service = new CatalogSyndicationFallbackAwarePackageGateService(
            $mappingService,
            $readinessService,
            $fallbackService,
            new CategorySyndicationFallbackAwarePackageGatePolicy(),
        );

        $event = $service->buildGatedPublishPackage(
            'pkg-1',
            'dest-1',
            'cat-1',
            'v1',
            'per_locale',
            ['title' => 'Category One'],
            ['title' => 'title'],
            ['title'],
            'actor-1',
            'test',
        );

        /** @var array{strictPublishable:bool,fallbackPublishable:bool,warnings:list<string>,fallbackMatchedBindingIds:list<string>} $payload */
        $payload = $event->payload();
        self::assertFalse($payload['strictPublishable']);
        self::assertTrue($payload['fallbackPublishable']);
        self::assertContains('package_publishable_via_fallback_only', $payload['warnings']);
        self::assertSame(['bind-1'], $payload['fallbackMatchedBindingIds']);
    }
}
