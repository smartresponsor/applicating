<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Application\ApplicationPublishEligibility;
use App\Entity\Application;
use App\ServiceInterface\ApplicationPublishEligibilityServiceInterface;

final class ApplicationPublishEligibilityService implements ApplicationPublishEligibilityServiceInterface
{
    /** @return list<ApplicationPublishEligibility> */
    public function buildEligibilityMap(Application $application): array
    {
        $hasManifest = 0 !== $application->getManifests()->count();
        $hasApprovedManifest = false;
        foreach ($application->getManifests() as $manifest) {
            if ('approved' === $manifest->getGovernanceState()) {
                $hasApprovedManifest = true;
                break;
            }
        }

        $eligibility = [];
        foreach ($application->getReleases() as $release) {
            $reason = null;
            if ('published' === $release->getPublicationState()->value) {
                $reason = 'Release is already published.';
            } elseif (!$hasManifest) {
                $reason = 'Publish requires an attached manifest.';
            } elseif (!$hasApprovedManifest) {
                $reason = 'Publish requires an approved manifest.';
            }

            $eligibility[] = new ApplicationPublishEligibility(
                releaseId: $release->getId() ?? 0,
                eligible: null === $reason,
                reason: $reason,
            );
        }

        return $eligibility;
    }
}
