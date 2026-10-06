<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\Service;

use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Entity\ApplicationManifestEntity;
use App\Applicating\Entity\ApplicationReleaseEntity;
use App\Applicating\Service\ApplicationPublishEligibilityService;
use PHPUnit\Framework\TestCase;

final class ApplicationPublishEligibilityServiceTest extends TestCase
{
    public function testEligibilityReasonsAcrossManifestAndPublicationStates(): void
    {
        $service = new ApplicationPublishEligibilityService();

        $withoutManifest = $this->applicationWithRelease('without-manifest');
        self::assertSame(
            'Publish requires an attached manifest.',
            $service->buildEligibilityMap($withoutManifest)[0]->reason,
        );

        $reviewRequired = $this->applicationWithRelease('review-required');
        $reviewRequired->addManifest($this->manifest($reviewRequired, 'review_required'));
        self::assertSame(
            'Publish requires an approved manifest.',
            $service->buildEligibilityMap($reviewRequired)[0]->reason,
        );

        $approved = $this->applicationWithRelease('approved');
        $approved->addManifest($this->manifest($approved, 'approved'));
        $approvedEligibility = $service->buildEligibilityMap($approved)[0];
        self::assertTrue($approvedEligibility->eligible);
        self::assertNull($approvedEligibility->reason);

        $published = $this->applicationWithRelease('published');
        $published->addManifest($this->manifest($published, 'approved'));
        $publishedRelease = $published->getReleases()->first();
        self::assertInstanceOf(ApplicationReleaseEntity::class, $publishedRelease);
        $publishedRelease->publish();
        self::assertSame(
            'Release is already published.',
            $service->buildEligibilityMap($published)[0]->reason,
        );
    }

    private function applicationWithRelease(string $suffix): ApplicationEntity
    {
        $application = new ApplicationEntity(
            'Eligibility '.$suffix,
            'eligibility-'.$suffix,
            'applicating/eligibility-'.$suffix,
            'Applicating Labs',
            'Eligibility coverage.',
        );
        $application->addRelease(new ApplicationReleaseEntity(
            $application,
            '1.0.0',
            'stable',
            hash('sha256', $suffix),
            'https://downloads.example.test/'.$suffix.'/1.0.0.zip',
            'Eligibility release notes.',
        ));

        return $application;
    }

    private function manifest(ApplicationEntity $application, string $governanceState): ApplicationManifestEntity
    {
        return new ApplicationManifestEntity(
            $application,
            '1.0.0',
            'io.applicating.'.$governanceState,
            ['listing'],
            ['tenant:read'],
            ['bootstrap'],
            'default',
            $governanceState,
            ['governanceState' => $governanceState],
        );
    }
}
