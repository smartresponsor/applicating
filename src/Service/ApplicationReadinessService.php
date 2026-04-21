<?php

declare(strict_types=1);

namespace App\Applicating\Service;

use App\Applicating\DTO\Application\ApplicationReadiness;
use App\Applicating\DTO\Application\ApplicationReadinessSignals;
use App\Applicating\Repository\ApplicationRepository;
use App\Applicating\ServiceInterface\ApplicationPublishEligibilityServiceInterface;
use App\Applicating\ServiceInterface\ApplicationReadinessServiceInterface;

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
