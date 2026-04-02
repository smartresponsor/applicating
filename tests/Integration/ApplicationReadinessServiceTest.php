<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Service\ApplicationReadinessService;
use App\ServiceInterface\ApplicationPublishEligibilityServiceInterface;
use PHPUnit\Framework\TestCase;

final class ApplicationReadinessServiceTest extends TestCase
{
    public function testBuildReadinessAggregatesBlockingReasons(): void
    {
        $eligibilityService = new class() implements ApplicationPublishEligibilityServiceInterface {
            public function buildEligibilityMap(string $applicationSlug): array
            {
                return [
                    'manifest' => [
                        'status' => 'blocked',
                        'message' => 'Missing manifest',
                    ],
                    'governance' => [
                        'status' => 'ok',
                    ],
                ];
            }
        };

        $service = new ApplicationReadinessService($eligibilityService);

        $readiness = $service->buildReadiness('test-app');

        self::assertFalse($readiness->canPublish);
        self::assertSame(['Missing manifest'], $readiness->blockingReasons);
    }
}
