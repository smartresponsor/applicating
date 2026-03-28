<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Policy\CategoryPublicationQualityPolicy;
use App\Service\CatalogPublicationQualityService;
use PHPUnit\Framework\TestCase;

final class CatalogPublicationQualityServiceTest extends TestCase
{
    public function testEvaluateReturnsAttentionPayloadForOptionalGaps(): void
    {
        $service = new CatalogPublicationQualityService(new CategoryPublicationQualityPolicy());

        $payload = $this->normalizePayload($service->evaluate(
            'category-701',
            78,
            [
                'slugReady' => true,
                'seoReady' => true,
                'contentReady' => true,
                'localeReady' => true,
                'mediaReady' => false,
                'aliasReady' => false,
            ],
            [
                'bannerReady' => false,
                'htmlBlockReady' => true,
            ],
            'operator-1',
            'pre-publish quality evaluation',
        )->payload());

        self::assertTrue($payload['publishableQuality']);
        self::assertSame('attention', $payload['riskLevel']);
        self::assertContains('mediaReady', $payload['softWarnings']);
        self::assertContains('aliasReady', $payload['softWarnings']);
        self::assertContains('qualityScoreBelowTarget', $payload['softWarnings']);
        self::assertContains('bannerReady', $payload['advisoryWarnings']);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array{publishableQuality: bool, riskLevel: string, softWarnings: list<string>, advisoryWarnings: list<string>}
     */
    private function normalizePayload(array $payload): array
    {
        return [
            'publishableQuality' => (bool) ($payload['publishableQuality'] ?? false),
            'riskLevel' => $this->scalarString($payload['riskLevel'] ?? ''),
            'softWarnings' => $this->stringList($payload['softWarnings'] ?? []),
            'advisoryWarnings' => $this->stringList($payload['advisoryWarnings'] ?? []),
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
