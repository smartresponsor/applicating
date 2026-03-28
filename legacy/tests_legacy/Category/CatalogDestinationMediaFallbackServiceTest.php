<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Policy\CategoryDestinationMediaFallbackPolicy;
use App\Policy\CategorySyndicationDestinationPolicy;
use App\Repository\CategoryMediaBindingRepository;
use App\Repository\CategorySyndicationDestinationRepository;
use App\Service\CatalogDestinationMediaFallbackService;
use App\Service\CatalogSyndicationDestinationService;
use PHPUnit\Framework\TestCase;

final class CatalogDestinationMediaFallbackServiceTest extends TestCase
{
    public function testEvaluateBuildsFallbackAwareDestinationMediaReport(): void
    {
        $bindingRepository = new CategoryMediaBindingRepository();
        $destinationRepository = new CategorySyndicationDestinationRepository();

        $destinationService = new CatalogSyndicationDestinationService(new CategorySyndicationDestinationPolicy(), $destinationRepository);
        $service = new CatalogDestinationMediaFallbackService($destinationRepository, $bindingRepository, new CategoryDestinationMediaFallbackPolicy());

        $bindingRepository->save(new \App\Entity\CategoryMediaBinding(
            'bind-global-primary',
            'category-1802',
            'asset-primary',
            \App\ValueObject\CategoryMediaRole::primary(),
            [],
            [],
            true,
            true,
            [],
            'operator-1',
            new \DateTimeImmutable('now'),
        ));

        $destinationService->register(
            'destination-1802',
            'Storefront CA French',
            'storefront',
            'push',
            true,
            ['channel' => 'storefront', 'locale' => 'fr_CA', 'requiredMediaRoles' => ['primary']],
            'operator-1',
            'register destination'
        );

        $payload = $this->normalizePayload($service->evaluate('destination-1802', 'category-1802', 'operator-9', 'evaluate fallback')->payload());
        self::assertFalse($payload['publishable']);
        self::assertTrue($payload['publishableWithFallback']);
        self::assertSame(['bind-global-primary'], $payload['fallbackMatchedBindingIds']);
        self::assertSame([], $payload['requiredMissing']);
        self::assertContains('sharedFallbackUsed', $payload['warnings']);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array{publishable: bool, publishableWithFallback: bool, fallbackMatchedBindingIds: list<string>, requiredMissing: list<string>, warnings: list<string>}
     */
    private function normalizePayload(array $payload): array
    {
        return [
            'publishable' => (bool) ($payload['publishable'] ?? false),
            'publishableWithFallback' => (bool) ($payload['publishableWithFallback'] ?? false),
            'fallbackMatchedBindingIds' => $this->stringList($payload['fallbackMatchedBindingIds'] ?? []),
            'requiredMissing' => $this->stringList($payload['requiredMissing'] ?? []),
            'warnings' => $this->stringList($payload['warnings'] ?? []),
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
