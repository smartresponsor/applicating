<?php

declare(strict_types=1);
/**
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>.
 */

namespace App\Tests\Category;

use App\Entity\CategoryMediaBinding;
use App\Entity\CategorySyndicationDestination;
use App\Policy\CategoryDestinationMediaFallbackPolicy;
use App\Policy\CategoryDestinationMediaPolicyPreferencePolicy;
use App\Policy\CategoryDestinationMediaReadinessPolicy;
use App\Repository\CategoryMediaBindingRepository;
use App\Repository\CategorySyndicationDestinationRepository;
use App\Service\CatalogDestinationMediaFallbackService;
use App\Service\CatalogDestinationMediaPolicyPreferenceService;
use App\Service\CatalogDestinationMediaReadinessService;
use App\Service\CatalogMediaApplicabilityService;
use App\ValueObject\CategoryMediaRole;
use PHPUnit\Framework\TestCase;

final class CatalogDestinationMediaPolicyPreferenceServiceTest extends TestCase
{
    public function testEvaluateResolvesPublishableViaFallbackWhenDestinationAllowsIt(): void
    {
        $destinationRepository = new CategorySyndicationDestinationRepository();
        $destinationRepository->save(CategorySyndicationDestination::register(
            'destination-1901',
            'Storefront Feed',
            'storefront',
            'push',
            true,
            [
                'channel' => 'storefront',
                'locale' => 'en_US',
                'requiredMediaRoles' => ['primary'],
                'mediaPolicyMode' => 'allow_fallback',
            ],
            'operator-1',
        ));

        $bindingRepository = new CategoryMediaBindingRepository();
        $bindingRepository->save(new CategoryMediaBinding(
            'binding-global-primary',
            'category-1901',
            'asset-global-primary',
            CategoryMediaRole::primary(),
            [],
            [],
            true,
            true,
            [],
            'operator-1',
            new \DateTimeImmutable('now'),
        ));

        $service = new CatalogDestinationMediaPolicyPreferenceService(
            $destinationRepository,
            new CatalogDestinationMediaReadinessService($destinationRepository, new CatalogMediaApplicabilityService($bindingRepository, new \App\Policy\CategoryMediaApplicabilityPolicy()), new CategoryDestinationMediaReadinessPolicy()),
            new CatalogDestinationMediaFallbackService($destinationRepository, $bindingRepository, new CategoryDestinationMediaFallbackPolicy()),
            new CategoryDestinationMediaPolicyPreferencePolicy(),
        );

        $payload = $this->normalizePayload($service->evaluate('destination-1901', 'category-1901', 'operator-1', 'step08')->payload());
        self::assertSame('allow_fallback', $payload['mediaPolicyMode']);
        self::assertFalse($payload['strictPublishable']);
        self::assertTrue($payload['fallbackPublishable']);
        self::assertTrue($payload['resolvedPublishable']);
        self::assertTrue($payload['checks']['fallbackAcceptedByPolicy']);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array{mediaPolicyMode: string, strictPublishable: bool, fallbackPublishable: bool, resolvedPublishable: bool, checks: array{fallbackAcceptedByPolicy: bool}}
     */
    private function normalizePayload(array $payload): array
    {
        $checks = is_array($payload['checks'] ?? null) ? $payload['checks'] : [];

        return [
            'mediaPolicyMode' => $this->scalarString($payload['mediaPolicyMode'] ?? ''),
            'strictPublishable' => (bool) ($payload['strictPublishable'] ?? false),
            'fallbackPublishable' => (bool) ($payload['fallbackPublishable'] ?? false),
            'resolvedPublishable' => (bool) ($payload['resolvedPublishable'] ?? false),
            'checks' => [
                'fallbackAcceptedByPolicy' => (bool) ($checks['fallbackAcceptedByPolicy'] ?? false),
            ],
        ];
    }

    private function scalarString(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
