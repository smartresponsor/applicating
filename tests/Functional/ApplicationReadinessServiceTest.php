<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Functional;

use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Entity\ApplicationManifestEntity;
use App\Applicating\Entity\ApplicationReleaseEntity;
use App\Applicating\Service\ApplicationReadinessService;
use App\Applicating\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ApplicationReadinessServiceTest extends KernelTestCase
{
    public function testMissingApplicationProducesBlockingReadiness(): void
    {
        [$service] = $this->bootReadinessService();

        $readiness = $service->buildReadiness('missing-application');

        self::assertFalse($readiness->canPublish);
        self::assertSame(['Application not found.'], $readiness->blockingReasons);
        self::assertFalse($readiness->signals->applicationFound);
        self::assertSame('missing-application', $readiness->signals->applicationSlug);
        self::assertSame(0, $readiness->signals->releaseCount);
        self::assertSame(0, $readiness->signals->manifestCount);
        self::assertFalse($readiness->signals->approvedManifestPresent);
        self::assertSame([], $readiness->signals->eligibility);
    }

    public function testUnapprovedManifestBlocksRelease(): void
    {
        [$service, $entityManager] = $this->bootReadinessService();
        $application = $this->application('review-readiness');
        $release = $this->release($application, '1.0.0');
        $manifest = $this->manifest($application, 'review_required');

        $application->addRelease($release);
        $application->addManifest($manifest);
        $entityManager->persist($application);
        $entityManager->persist($release);
        $entityManager->persist($manifest);
        $entityManager->flush();

        $readiness = $service->buildReadiness('review-readiness');

        self::assertFalse($readiness->canPublish);
        self::assertSame(['Publish requires an approved manifest.'], $readiness->blockingReasons);
        self::assertTrue($readiness->signals->applicationFound);
        self::assertSame(1, $readiness->signals->releaseCount);
        self::assertSame(1, $readiness->signals->manifestCount);
        self::assertFalse($readiness->signals->approvedManifestPresent);
        self::assertCount(1, $readiness->signals->eligibility);
        self::assertFalse($readiness->signals->eligibility[0]['eligible']);
    }

    public function testApprovedManifestMakesReleasePublishEligible(): void
    {
        [$service, $entityManager] = $this->bootReadinessService();
        $application = $this->application('approved-readiness');
        $release = $this->release($application, '2.0.0');
        $manifest = $this->manifest($application, 'approved');

        $application->addRelease($release);
        $application->addManifest($manifest);
        $entityManager->persist($application);
        $entityManager->persist($release);
        $entityManager->persist($manifest);
        $entityManager->flush();

        $readiness = $service->buildReadiness('approved-readiness');

        self::assertTrue($readiness->canPublish);
        self::assertSame([], $readiness->blockingReasons);
        self::assertTrue($readiness->signals->approvedManifestPresent);
        self::assertCount(1, $readiness->signals->eligibility);
        self::assertTrue($readiness->signals->eligibility[0]['eligible']);
        self::assertStringContainsString('publish-eligible', $readiness->signals->eligibility[0]['reason']);
    }

    /** @return array{ApplicationReadinessService, EntityManagerInterface} */
    private function bootReadinessService(): array
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();
        DoctrineSchemaResetter::reset($entityManager);

        /** @var ApplicationReadinessService $service */
        $service = $container->get(ApplicationReadinessService::class);

        return [$service, $entityManager];
    }

    private function application(string $slug): ApplicationEntity
    {
        return new ApplicationEntity(
            'Readiness Application',
            $slug,
            'applicating/'.$slug,
            'Applicating Labs',
            'Readiness test summary',
        );
    }

    private function release(ApplicationEntity $application, string $version): ApplicationReleaseEntity
    {
        return new ApplicationReleaseEntity(
            $application,
            $version,
            'stable',
            hash('sha256', $version),
            'https://downloads.example.test/'.$version.'.zip',
            'Readiness release notes',
        );
    }

    private function manifest(ApplicationEntity $application, string $governanceState): ApplicationManifestEntity
    {
        return new ApplicationManifestEntity(
            $application,
            '1.0.0',
            'io.applicating.readiness.'.$governanceState,
            ['listing'],
            ['tenant:read'],
            ['bootstrap'],
            'default',
            $governanceState,
            ['governanceState' => $governanceState],
        );
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }
}
