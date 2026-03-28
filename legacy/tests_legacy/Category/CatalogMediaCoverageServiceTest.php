<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Policy\CategoryMediaCoveragePolicy;
use App\Policy\CategoryMediaGovernancePolicy;
use App\Repository\CategoryMediaBindingRepository;
use App\Service\CatalogMediaCoverageService;
use App\Service\CatalogMediaGovernanceService;
use PHPUnit\Framework\TestCase;

final class CatalogMediaCoverageServiceTest extends TestCase
{
    public function testEvaluateReturnsGovernedMediaChecks(): void
    {
        $repository = new CategoryMediaBindingRepository();
        $governance = new CatalogMediaGovernanceService($repository, new CategoryMediaGovernancePolicy());
        $coverage = new CatalogMediaCoverageService($repository, new CategoryMediaCoveragePolicy());

        $governance->bind('bind-1', 'category-901', 'asset-primary', 'primary', ['storefront'], ['en_US'], true, true, [], 'operator-1', 'bind primary');
        $governance->bind('bind-2', 'category-901', 'asset-banner', 'banner', ['storefront'], ['en_US'], false, true, [], 'operator-1', 'bind banner');

        $payload = $this->normalizePayload($coverage->evaluate('category-901', [
            'media' => ['primaryAssetId' => ''],
            'presentation' => ['bannerId' => ''],
        ], 'operator-2', 'media readiness review')->payload());

        self::assertSame([], $payload['requiredMissing']);
        self::assertTrue($payload['checks']['mediaReady']);
        self::assertTrue($payload['checks']['bannerReady']);
        self::assertTrue($payload['checks']['requiredMediaCoverageReady']);
        self::assertContains('heroReady', $payload['warnings']);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array{requiredMissing: list<string>, warnings: list<string>, checks: array{mediaReady: bool, bannerReady: bool, requiredMediaCoverageReady: bool}}
     */
    private function normalizePayload(array $payload): array
    {
        $checks = is_array($payload['checks'] ?? null) ? $payload['checks'] : [];

        return [
            'requiredMissing' => $this->stringList($payload['requiredMissing'] ?? []),
            'warnings' => $this->stringList($payload['warnings'] ?? []),
            'checks' => [
                'mediaReady' => (bool) ($checks['mediaReady'] ?? false),
                'bannerReady' => (bool) ($checks['bannerReady'] ?? false),
                'requiredMediaCoverageReady' => (bool) ($checks['requiredMediaCoverageReady'] ?? false),
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
