<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Policy\CategoryCompletenessPolicy;
use App\Service\CatalogCompletenessService;
use PHPUnit\Framework\TestCase;

final class CatalogCompletenessServiceTest extends TestCase
{
    public function testEvaluateReturnsCompleteReportWithPublicationChecks(): void
    {
        $service = new CatalogCompletenessService(new CategoryCompletenessPolicy());

        $event = $service->evaluate('category-601', [
            'slug' => 'outdoor-lights',
            'seo' => [
                'title' => 'Outdoor Lights',
                'description' => 'Shop outdoor lights',
            ],
            'content' => [
                'body' => 'Long-form merchandising copy',
            ],
            'locale' => [
                'enabled' => ['en_US'],
            ],
            'media' => [
                'primaryAssetId' => 'asset-1',
            ],
            'aliases' => ['garden-lights'],
            'presentation' => [
                'bannerId' => 'banner-1',
                'htmlBlockId' => 'html-1',
            ],
        ], 'operator-1', 'completeness review');

        $payload = $this->normalizeCompletenessPayload($event->payload());
        self::assertTrue($payload['complete']);
        self::assertSame(100, $payload['score']);
        self::assertSame([], $payload['missingRequired']);
        self::assertTrue($payload['publicationChecks']['seoReady']);
        self::assertTrue($payload['publicationChecks']['localeReady']);
    }

    public function testEvaluateReturnsMissingRequiredAndWarningsForIncompletePayload(): void
    {
        $service = new CatalogCompletenessService(new CategoryCompletenessPolicy());

        $event = $service->evaluate('category-602', [
            'slug' => '',
            'seo' => [
                'title' => '',
                'description' => '',
            ],
            'content' => [
                'body' => '',
            ],
            'locale' => [
                'enabled' => [],
            ],
            'media' => [
                'primaryAssetId' => '',
            ],
            'aliases' => [],
            'presentation' => [
                'bannerId' => '',
                'htmlBlockId' => '',
            ],
        ], 'operator-2', 'pre-publish audit');

        $payload = $this->normalizeCompletenessPayload($event->payload());
        self::assertFalse($payload['complete']);
        self::assertContains('slugReady', $payload['missingRequired']);
        self::assertContains('seoTitleReady', $payload['missingRequired']);
        self::assertContains('mediaReady', $payload['warnings']);
        self::assertFalse($payload['publicationChecks']['seoReady']);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array{
     *     complete: bool,
     *     score: int,
     *     missingRequired: list<string>,
     *     warnings: list<string>,
     *     publicationChecks: array{seoReady: bool, localeReady: bool}
     * }
     */
    private function normalizeCompletenessPayload(array $payload): array
    {
        return [
            'complete' => (bool) ($payload['complete'] ?? false),
            'score' => $this->scalarInt($payload['score'] ?? 0),
            'missingRequired' => $this->stringList($payload['missingRequired'] ?? []),
            'warnings' => $this->stringList($payload['warnings'] ?? []),
            'publicationChecks' => [
                'seoReady' => (bool) ((is_array($payload['publicationChecks'] ?? null) ? $payload['publicationChecks'] : [])['seoReady'] ?? false),
                'localeReady' => (bool) ((is_array($payload['publicationChecks'] ?? null) ? $payload['publicationChecks'] : [])['localeReady'] ?? false),
            ],
        ];
    }

    /**
     * @return list<string>
     */
    private function stringList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_map(fn (mixed $item): string => $this->scalarString($item), $value));
    }

    private function scalarInt(mixed $value): int
    {
        return is_int($value) ? $value : (is_numeric($value) ? (int) $value : 0);
    }

    private function scalarString(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
