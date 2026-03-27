<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Policy\CategoryDestinationMediaReadinessPolicy;
use App\Policy\CategoryMediaApplicabilityPolicy;
use App\Policy\CategoryMediaGovernancePolicy;
use App\Policy\CategorySyndicationDestinationPolicy;
use App\Repository\CategoryMediaBindingRepository;
use App\Repository\CategorySyndicationDestinationRepository;
use App\Service\CatalogDestinationMediaReadinessService;
use App\Service\CatalogMediaApplicabilityService;
use App\Service\CatalogMediaGovernanceService;
use App\Service\CatalogSyndicationDestinationService;
use PHPUnit\Framework\TestCase;

final class CatalogDestinationMediaReadinessServiceTest extends TestCase
{
    public function testEvaluateBuildsDestinationScopedMediaReadiness(): void
    {
        $bindingRepository = new CategoryMediaBindingRepository();
        $destinationRepository = new CategorySyndicationDestinationRepository();

        $governance = new CatalogMediaGovernanceService($bindingRepository, new CategoryMediaGovernancePolicy());
        $destinationService = new CatalogSyndicationDestinationService(new CategorySyndicationDestinationPolicy(), $destinationRepository);
        $applicability = new CatalogMediaApplicabilityService($bindingRepository, new CategoryMediaApplicabilityPolicy());
        $service = new CatalogDestinationMediaReadinessService($destinationRepository, $applicability, new CategoryDestinationMediaReadinessPolicy());

        $governance->bind('bind-primary', 'category-1301', 'asset-primary', 'primary', ['storefront'], ['en_US'], true, true, [], 'operator-1', 'primary');
        $governance->bind('bind-hero', 'category-1301', 'asset-hero', 'hero', ['storefront'], ['en_US'], false, true, [], 'operator-1', 'hero');

        $destinationService->register(
            'destination-1301',
            'Storefront US',
            'storefront',
            'push',
            true,
            ['channel' => 'storefront', 'locale' => 'en_US', 'requiredMediaRoles' => ['primary', 'hero']],
            'operator-1',
            'register destination'
        );

        $payload = $this->normalizePayload($service->evaluate('destination-1301', 'category-1301', 'operator-9', 'destination media readiness')->payload());

        self::assertTrue($payload['publishable']);
        self::assertTrue($payload['checks']['destinationMediaPublishable']);
        self::assertSame('storefront', $payload['channel']);
        self::assertSame('en_US', $payload['locale']);
        self::assertSame(['bind-primary', 'bind-hero'], $payload['matchedBindingIds']);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array{publishable: bool, channel: string, locale: string, matchedBindingIds: list<string>, checks: array{destinationMediaPublishable: bool}}
     */
    private function normalizePayload(array $payload): array
    {
        $checks = is_array($payload['checks'] ?? null) ? $payload['checks'] : [];

        return [
            'publishable' => (bool) ($payload['publishable'] ?? false),
            'channel' => $this->scalarString($payload['channel'] ?? ''),
            'locale' => $this->scalarString($payload['locale'] ?? ''),
            'matchedBindingIds' => $this->stringList($payload['matchedBindingIds'] ?? []),
            'checks' => [
                'destinationMediaPublishable' => (bool) ($checks['destinationMediaPublishable'] ?? false),
            ],
        ];
    }

    /** @return list<string> */
    private function stringList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_map(fn (mixed $item): string => $this->scalarString($item), $value));
    }

    private function scalarString(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
