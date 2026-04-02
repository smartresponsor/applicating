<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Application\ApplicationReadiness;
use App\ServiceInterface\ApplicationPublishEligibilityServiceInterface;
use App\ServiceInterface\ApplicationReadinessServiceInterface;

final class ApplicationReadinessService implements ApplicationReadinessServiceInterface
{
    public function __construct(
        private readonly ApplicationPublishEligibilityServiceInterface $eligibilityService,
    ) {
    }

    public function buildReadiness(string $applicationSlug): ApplicationReadiness
    {
        $eligibility = $this->eligibilityService->buildEligibilityMap($applicationSlug);

        $blocking = [];
        $warnings = [];

        foreach ($eligibility as $key => $item) {
            if (($item['status'] ?? null) === 'blocked') {
                $blocking[] = (string) ($item['message'] ?? $key);
            }

            if (($item['status'] ?? null) === 'warning') {
                $warnings[] = (string) ($item['message'] ?? $key);
            }
        }

        return new ApplicationReadiness(
            canPublish: empty($blocking),
            blockingReasons: $blocking,
            warnings: $warnings,
            signals: $eligibility,
        );
    }
}
