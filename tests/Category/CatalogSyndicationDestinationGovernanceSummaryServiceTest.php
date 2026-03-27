<?php

declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 * Owner: Marketing America Corp
 */

namespace App\Tests\Category;

use App\Policy\CategorySyndicationDestinationGovernanceSummaryPolicy;
use App\Service\CatalogSyndicationDestinationGovernanceSummaryService;
use PHPUnit\Framework\TestCase;

final class CatalogSyndicationDestinationGovernanceSummaryServiceTest extends TestCase
{
    public function testBuildSummaryAggregatesDestinationGovernanceTrailPayloads(): void
    {
        $service = new CatalogSyndicationDestinationGovernanceSummaryService(new CategorySyndicationDestinationGovernanceSummaryPolicy());

        $event = $service->buildSummary('dst-1', [
            [
                'deliveryStatus' => 'delivered',
                'mediaPolicyMode' => 'strict_exact',
                'resolvedPublishable' => true,
                'fallbackUsed' => false,
                'retryable' => false,
                'retryScheduled' => false,
                'warnings' => [],
                'checks' => [
                    'governanceTrailHasFailures' => false,
                    'governanceTrailHasDelivered' => true,
                ],
            ],
            [
                'deliveryStatus' => 'retry_scheduled',
                'mediaPolicyMode' => 'prefer_exact_warn',
                'resolvedPublishable' => true,
                'fallbackUsed' => true,
                'retryable' => true,
                'retryScheduled' => true,
                'warnings' => ['governance_trail_fallback_used'],
                'checks' => [
                    'governanceTrailHasFailures' => true,
                    'governanceTrailHasDelivered' => false,
                ],
            ],
        ], 'actor-1', 'test');

        $payload = $this->normalizeSummaryPayload($event->payload());

        self::assertSame('dst-1', $payload['destinationId']);
        self::assertSame(2, $payload['totalTrails']);
        self::assertSame(2, $payload['resolvedPublishableCount']);
        self::assertSame(1, $payload['fallbackUsedCount']);
        self::assertSame(1, $payload['retryScheduledCount']);
        self::assertSame(1, $payload['statusCounts']['delivered']);
        self::assertSame(1, $payload['statusCounts']['retry_scheduled']);
        self::assertSame(1, $payload['policyModeCounts']['prefer_exact_warn']);
        self::assertContains('governance_trail_fallback_used', $payload['warningCodes']);
        self::assertTrue($payload['checks']['destinationGovernanceSummaryHasDelivered']);
        self::assertTrue($payload['checks']['destinationGovernanceSummaryHasFailures']);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array{
     *     destinationId: string,
     *     totalTrails: int,
     *     resolvedPublishableCount: int,
     *     fallbackUsedCount: int,
     *     retryScheduledCount: int,
     *     statusCounts: array<string, int>,
     *     policyModeCounts: array<string, int>,
     *     warningCodes: list<string>,
     *     checks: array{destinationGovernanceSummaryHasDelivered: bool, destinationGovernanceSummaryHasFailures: bool}
     * }
     */
    private function normalizeSummaryPayload(array $payload): array
    {
        return [
            'destinationId' => $this->scalarString($payload['destinationId'] ?? ''),
            'totalTrails' => $this->scalarInt($payload['totalTrails'] ?? 0),
            'resolvedPublishableCount' => $this->scalarInt($payload['resolvedPublishableCount'] ?? 0),
            'fallbackUsedCount' => $this->scalarInt($payload['fallbackUsedCount'] ?? 0),
            'retryScheduledCount' => $this->scalarInt($payload['retryScheduledCount'] ?? 0),
            'statusCounts' => $this->intMap($payload['statusCounts'] ?? []),
            'policyModeCounts' => $this->intMap($payload['policyModeCounts'] ?? []),
            'warningCodes' => $this->stringList($payload['warningCodes'] ?? []),
            'checks' => [
                'destinationGovernanceSummaryHasDelivered' => (bool) ((is_array($payload['checks'] ?? null) ? $payload['checks'] : [])['destinationGovernanceSummaryHasDelivered'] ?? false),
                'destinationGovernanceSummaryHasFailures' => (bool) ((is_array($payload['checks'] ?? null) ? $payload['checks'] : [])['destinationGovernanceSummaryHasFailures'] ?? false),
            ],
        ];
    }

    /**
     * @return array<string, int>
     */
    private function intMap(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $normalized = [];
        foreach ($value as $key => $item) {
            $normalized[$this->scalarString($key)] = $this->scalarInt($item);
        }

        return $normalized;
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
