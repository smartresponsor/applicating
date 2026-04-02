<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Application\ApplicationReadiness;
use App\Repository\ApplicationRepository;
use App\ServiceInterface\ApplicationPublishEligibilityServiceInterface;
use App\ServiceInterface\ApplicationReadinessServiceInterface;

final class ApplicationReadinessService implements ApplicationReadinessServiceInterface
{
    public function __construct(
        private readonly ApplicationRepository $applicationRepository,
        private readonly ApplicationPublishEligibilityServiceInterface $eligibilityService,
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
                signals: [
                    'applicationFound' => false,
                    'applicationSlug' => $applicationSlug,
                ],
            );
        }

        $eligibility = $this->eligibilityService->buildEligibilityMap($application);

        $blocking = [];
        foreach ($eligibility as $releaseId => $item) {
            if (($item['eligible'] ?? false) !== true) {
                $blocking[] = (string) ($item['reason'] ?? sprintf('Release %s is not publish-eligible.', (string) $releaseId));
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
            signals: [
                'applicationFound' => true,
                'applicationSlug' => $application->getSlug(),
                'releaseCount' => $application->getReleases()->count(),
                'manifestCount' => $application->getManifests()->count(),
                'approvedManifestPresent' => $approvedManifestPresent,
                'eligibility' => $eligibility,
            ],
        );
    }
}
