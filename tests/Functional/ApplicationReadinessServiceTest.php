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

    public function testApplicationWithoutReleasesIsNotPublishReady(): void
    {
        [$service, $entityManager] = $this->bootReadinessService();
        $application = $this->application('empty-readiness');
        $entityManager->persist($application);
        $entityManager->flush();

        $readiness = $service->buildReadiness('empty-readiness');

        self::assertFalse($readiness->canPublish);
        self::assertSame(['Publish requires at least one release.'], $readiness->blockingReasons);
        self::assertTrue($readiness->signals->applicationFound);
        self::assertSame(0, $readiness->signals->releaseCount);
        self::assertSame([], $readiness->signals->eligibility);
    }

    public function testApprovedManifestWithoutReleaseStillBlocksPublication(): void
    {
        [$service, $entityManager] = $this->bootReadinessService();
        $application = $this->application('manifest-only-readiness');
        $manifest = $this->manifest($application, 'approved');

        $application->addManifest($manifest);
        $entityManager->persist($application);
        $entityManager->persist($manifest);
        $entityManager->flush();

        $readiness = $service->buildReadiness('manifest-only-readiness');

        self::assertFalse($readiness->canPublish);
        self::assertSame(['Publish requires at least one release.'], $readiness->blockingReasons);
        self::assertTrue($readiness->signals->approvedManifestPresent);
        self::assertSame(1, $readiness->signals->manifestCount);
        self::assertSame(0, $readiness->signals->releaseCount);
    }

    public function testMissingManifestBlocksRelease(): void
    {
        [$service, $entityManager] = $this->bootReadinessService();
        $application = $this->application('missing-manifest-readiness');
        $release = $this->release($application, '1.1.0');

        $application->addRelease($release);
        $entityManager->persist($application);
        $entityManager->persist($release);
        $entityManager->flush();

        $readiness = $service->buildReadiness('missing-manifest-readiness');

        self::assertFalse($readiness->canPublish);
        self::assertSame(['Publish requires an attached manifest.'], $readiness->blockingReasons);
        self::assertSame(1, $readiness->signals->releaseCount);
        self::assertSame(0, $readiness->signals->manifestCount);
        self::assertFalse($readiness->signals->eligibility[0]['eligible']);
    }

    public function testAlreadyPublishedReleaseIsNotPublishReady(): void
    {
        [$service, $entityManager] = $this->bootReadinessService();
        $application = $this->application('published-readiness');
        $release = $this->release($application, '3.0.0');
        $release->publish();
        $manifest = $this->manifest($application, 'approved');

        $application->addRelease($release);
        $application->addManifest($manifest);
        $entityManager->persist($application);
        $entityManager->persist($release);
        $entityManager->persist($manifest);
        $entityManager->flush();

        $readiness = $service->buildReadiness('published-readiness');

        self::assertFalse($readiness->canPublish);
        self::assertSame(['Release is already published.'], $readiness->blockingReasons);
        self::assertTrue($readiness->signals->approvedManifestPresent);
        self::assertFalse($readiness->signals->eligibility[0]['eligible']);
    }

    public function testPublishedReleaseDoesNotBlockAnotherEligibleRelease(): void
    {
        [$service, $entityManager] = $this->bootReadinessService();
        $application = $this->application('mixed-readiness');
        $published = $this->release($application, '1.0.0');
        $published->publish();
        $draft = $this->release($application, '2.0.0');
        $manifest = $this->manifest($application, 'approved');

        $application->addRelease($published);
        $application->addRelease($draft);
        $application->addManifest($manifest);
        $entityManager->persist($application);
        $entityManager->persist($published);
        $entityManager->persist($draft);
        $entityManager->persist($manifest);
        $entityManager->flush();

        $readiness = $service->buildReadiness('mixed-readiness');

        self::assertTrue($readiness->canPublish);
        self::assertSame([], $readiness->blockingReasons);
        self::assertSame(2, $readiness->signals->releaseCount);
        self::assertCount(2, $readiness->signals->eligibility);
        self::assertCount(1, array_filter(
            $readiness->signals->eligibility,
            static fn (array $item): bool => true === $item['eligible'],
        ));
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
