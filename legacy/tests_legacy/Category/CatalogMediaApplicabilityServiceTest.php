<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Policy\CategoryMediaApplicabilityPolicy;
use App\Policy\CategoryMediaGovernancePolicy;
use App\Repository\CategoryMediaBindingRepository;
use App\Service\CatalogMediaApplicabilityService;
use App\Service\CatalogMediaGovernanceService;
use PHPUnit\Framework\TestCase;

final class CatalogMediaApplicabilityServiceTest extends TestCase
{
    public function testEvaluateReturnsChannelAndLocaleScopedCoverage(): void
    {
        $repository = new CategoryMediaBindingRepository();
        $governance = new CatalogMediaGovernanceService($repository, new CategoryMediaGovernancePolicy());
        $applicability = new CatalogMediaApplicabilityService($repository, new CategoryMediaApplicabilityPolicy());

        $governance->bind('bind-us-primary', 'category-1201', 'asset-primary-us', 'primary', ['storefront'], ['en_US'], true, true, [], 'operator-1', 'bind US primary');
        $governance->bind('bind-us-banner', 'category-1201', 'asset-banner-us', 'banner', ['storefront'], ['en_US'], false, true, [], 'operator-1', 'bind US banner');
        $governance->bind('bind-fr-primary', 'category-1201', 'asset-primary-fr', 'primary', ['storefront'], ['fr_FR'], true, true, [], 'operator-1', 'bind FR primary');

        $event = $applicability->evaluate('category-1201', [
            'channel' => 'storefront',
            'locale' => 'en_US',
            'requiredRoles' => ['primary', 'banner'],
        ], 'operator-2', 'evaluate scoped media');

        /** @var array{requiredMissing:list<mixed>, warnings:list<string>, checks:array<string,bool>, matchedBindingIds:list<string>} $payload */
        $payload = $event->payload();
        self::assertSame([], $payload['requiredMissing']);
        self::assertTrue($payload['checks']['channelScopedMediaReady']);
        self::assertTrue($payload['checks']['localeScopedMediaReady']);
        self::assertTrue($payload['checks']['requiredRoleCoverageReady']);
        self::assertCount(2, $payload['matchedBindingIds']);
    }

    public function testEvaluateReportsMissingScopedRoleCoverage(): void
    {
        $repository = new CategoryMediaBindingRepository();
        $governance = new CatalogMediaGovernanceService($repository, new CategoryMediaGovernancePolicy());
        $applicability = new CatalogMediaApplicabilityService($repository, new CategoryMediaApplicabilityPolicy());

        $governance->bind('bind-search-icon', 'category-1202', 'asset-icon', 'icon', ['search'], ['en_US'], true, true, [], 'operator-1', 'bind search icon');

        $event = $applicability->evaluate('category-1202', [
            'channel' => 'storefront',
            'locale' => 'en_US',
            'requiredRoles' => ['primary'],
        ], 'operator-2', 'evaluate missing scoped role');

        /** @var array{requiredMissing:list<mixed>, warnings:list<string>, checks:array<string,bool>, matchedBindingIds:list<string>} $payload */
        $payload = $event->payload();
        self::assertContains('role:primary', $payload['requiredMissing']);
        self::assertContains('channelScopedMediaReady', $payload['requiredMissing']);
        self::assertFalse($payload['checks']['requiredRoleCoverageReady']);
        self::assertContains('exactChannelLocaleMatchReady', $payload['warnings']);
    }
}
