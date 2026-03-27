<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Policy\CategoryCompletenessPolicy;
use App\Policy\CategoryMediaCoveragePolicy;
use App\Policy\CategoryMediaGovernancePolicy;
use App\Repository\CategoryMediaBindingRepository;
use App\Service\CatalogMediaCompletenessBridgeService;
use App\Service\CatalogMediaCoverageService;
use App\Service\CatalogMediaGovernanceService;
use PHPUnit\Framework\TestCase;

final class CatalogMediaCompletenessBridgeServiceTest extends TestCase
{
    public function testEvaluateUsesGovernedMediaBindingsForCompletenessChecks(): void
    {
        $repository = new CategoryMediaBindingRepository();
        $governance = new CatalogMediaGovernanceService($repository, new CategoryMediaGovernancePolicy());
        $coverage = new CatalogMediaCoverageService($repository, new CategoryMediaCoveragePolicy());
        $service = new CatalogMediaCompletenessBridgeService(new CategoryCompletenessPolicy(), $coverage);

        $governance->bind('bind-11', 'category-902', 'asset-primary', 'primary', ['storefront'], ['en_US'], true, true, [], 'operator-1', 'bind primary');

        $event = $service->evaluate('category-902', [
            'slug' => 'winter-coats',
            'seo' => ['title' => 'Winter Coats', 'description' => 'Shop winter coats'],
            'content' => ['body' => 'Cold weather outerwear'],
            'locale' => ['enabled' => ['en_US']],
            'media' => ['primaryAssetId' => ''],
            'aliases' => ['coats-winter'],
            'presentation' => ['bannerId' => '', 'htmlBlockId' => 'html-1'],
        ], 'operator-2', 'completeness with governed media');

        $payload = $this->normalizePayload($event->payload());
        self::assertTrue($payload['complete']);
        self::assertTrue($payload['checks']['mediaReady']);
        self::assertTrue($payload['publicationChecks']['requiredMediaCoverageReady']);
        self::assertContains('bannerReady', $payload['warnings']);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array{complete: bool, warnings: list<string>, checks: array{mediaReady: bool}, publicationChecks: array{requiredMediaCoverageReady: bool}}
     */
    private function normalizePayload(array $payload): array
    {
        $checks = is_array($payload['checks'] ?? null) ? $payload['checks'] : [];
        $publicationChecks = is_array($payload['publicationChecks'] ?? null) ? $payload['publicationChecks'] : [];

        return [
            'complete' => (bool) ($payload['complete'] ?? false),
            'warnings' => $this->stringList($payload['warnings'] ?? []),
            'checks' => [
                'mediaReady' => (bool) ($checks['mediaReady'] ?? false),
            ],
            'publicationChecks' => [
                'requiredMediaCoverageReady' => (bool) ($publicationChecks['requiredMediaCoverageReady'] ?? false),
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
