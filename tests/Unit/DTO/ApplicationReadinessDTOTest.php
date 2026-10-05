<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\DTO;

use App\Applicating\DTO\ApplicationPublishEligibilityDTO;
use App\Applicating\DTO\ApplicationReadinessDTO;
use App\Applicating\DTO\ApplicationReadinessSignalsDTO;
use PHPUnit\Framework\TestCase;

final class ApplicationReadinessDTOTest extends TestCase
{
    public function testReadinessSerializesNestedSignals(): void
    {
        $eligibility = [
            7 => [
                'releaseId' => 42,
                'eligible' => true,
                'reason' => 'Ready.',
            ],
        ];
        $signals = new ApplicationReadinessSignalsDTO(
            true,
            'demo-application',
            2,
            1,
            true,
            $eligibility,
        );
        $readiness = new ApplicationReadinessDTO(
            false,
            ['A blocking reason.'],
            ['A warning.'],
            $signals,
        );

        self::assertTrue($signals->applicationFound);
        self::assertSame('demo-application', $signals->applicationSlug);
        self::assertSame(2, $signals->releaseCount);
        self::assertSame(1, $signals->manifestCount);
        self::assertTrue($signals->approvedManifestPresent);
        self::assertSame($eligibility, $signals->eligibility);
        self::assertSame([
            'applicationFound' => true,
            'applicationSlug' => 'demo-application',
            'releaseCount' => 2,
            'manifestCount' => 1,
            'approvedManifestPresent' => true,
            'eligibility' => $eligibility,
        ], $signals->toArray());

        self::assertFalse($readiness->canPublish);
        self::assertSame(['A blocking reason.'], $readiness->blockingReasons);
        self::assertSame(['A warning.'], $readiness->warnings);
        self::assertSame($signals, $readiness->signals);
        self::assertSame([
            'canPublish' => false,
            'blockingReasons' => ['A blocking reason.'],
            'warnings' => ['A warning.'],
            'signals' => $signals->toArray(),
        ], $readiness->toArray());
    }

    public function testPublishEligibilitySerializationCoversExplicitAndFallbackReasons(): void
    {
        $explicit = new ApplicationPublishEligibilityDTO(42, false, 'Manifest is not approved.');
        $fallback = new ApplicationPublishEligibilityDTO(43, true, null);

        self::assertSame([
            'releaseId' => 42,
            'eligible' => false,
            'reason' => 'Manifest is not approved.',
        ], $explicit->toReadinessArray());
        self::assertSame([
            'eligible' => false,
            'reason' => 'Manifest is not approved.',
        ], $explicit->toLegacyMapItem());

        self::assertSame([
            'releaseId' => 43,
            'eligible' => true,
            'reason' => 'Release 43 is publish-eligible.',
        ], $fallback->toReadinessArray());
        self::assertSame([
            'eligible' => true,
            'reason' => null,
        ], $fallback->toLegacyMapItem());
    }
}
