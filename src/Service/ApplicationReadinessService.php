<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\DTO\Application\ApplicationReadiness;
use App\Application\DTO\Application\ApplicationReadinessSignals;
use App\Application\Repository\ApplicationRepository;
use App\Application\ServiceInterface\ApplicationPublishEligibilityServiceInterface;
use App\Application\ServiceInterface\ApplicationReadinessServiceInterface;

final readonly class ApplicationReadinessService implements ApplicationReadinessServiceInterface
{
    public function __construct(
        private ApplicationRepository $applicationRepository,
        private ApplicationPublishEligibilityServiceInterface $eligibilityService,
    ) {
    }

    public function buildReadiness(string $applicationSlug): ApplicationReadiness
    {
        $application = $this->applicationRepository->findOneBy(['slug' => $applicationSlug]);
        if (null === $application) {
            return new ApplicationReadiness(
                canPublish: false,
                blockingReasons: ['Application not found.'],
                warnings: [],
                signals: new ApplicationReadinessSignals(
                    applicationFound: false,
                    applicationSlug: $applicationSlug,
                    releaseCount: 0,
                    manifestCount: 0,
                    approvedManifestPresent: false,
                    eligibility: [],
                ),
            );
        }

        $eligibilityItems = $this->eligibilityService->buildEligibilityMap($application);
        $eligibility = [];

        $blocking = [];
        foreach ($eligibilityItems as $item) {
            $eligibility[] = $item->toReadinessArray();

            if (!$item->eligible) {
                $blocking[] = $item->reason ?? sprintf('Release %d is not publish-eligible.', $item->releaseId);
            }
        }

        $approvedManifestPresent = false;
        foreach ($application->getManifests() as $manifest) {
            if ('approved' === $manifest->getGovernanceState()) {
                $approvedManifestPresent = true;
                break;
            }
        }

        return new ApplicationReadiness(
            canPublish: [] === $blocking,
            blockingReasons: array_values(array_unique($blocking)),
            warnings: [],
            signals: new ApplicationReadinessSignals(
                applicationFound: true,
                applicationSlug: $application->getSlug(),
                releaseCount: $application->getReleases()->count(),
                manifestCount: $application->getManifests()->count(),
                approvedManifestPresent: $approvedManifestPresent,
                eligibility: $eligibility,
            ),
        );
    }
}
