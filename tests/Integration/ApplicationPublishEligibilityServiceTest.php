<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\Application;
use App\Entity\ApplicationManifest;
use App\Entity\ApplicationRelease;
use App\ServiceInterface\ApplicationPublishEligibilityServiceInterface;
use App\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ApplicationPublishEligibilityServiceTest extends KernelTestCase
{
    private ApplicationPublishEligibilityServiceInterface $applicationPublishEligibilityService;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();
        DoctrineSchemaResetter::reset($entityManager);
        $this->entityManager = $entityManager;

        /** @var ApplicationPublishEligibilityServiceInterface $service */
        $service = $container->get(ApplicationPublishEligibilityServiceInterface::class);
        $this->applicationPublishEligibilityService = $service;
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }

    public function testBuildEligibilityMapRequiresManifest(): void
    {
        [$application, $release] = $this->createApplicationAggregate(null);

        $eligibility = $this->applicationPublishEligibilityService->buildEligibilityMap($application);

        self::assertFalse($eligibility[$release->getId()]['eligible']);
        self::assertSame('Publish requires an attached manifest.', $eligibility[$release->getId()]['reason']);
    }

    public function testBuildEligibilityMapRequiresApprovedManifest(): void
    {
        [$application, $release] = $this->createApplicationAggregate('review_required');

        $eligibility = $this->applicationPublishEligibilityService->buildEligibilityMap($application);

        self::assertFalse($eligibility[$release->getId()]['eligible']);
        self::assertSame('Publish requires an approved manifest.', $eligibility[$release->getId()]['reason']);
    }

    public function testBuildEligibilityMapAllowsEligibleRelease(): void
    {
        [$application, $release] = $this->createApplicationAggregate('approved');

        $eligibility = $this->applicationPublishEligibilityService->buildEligibilityMap($application);

        self::assertTrue($eligibility[$release->getId()]['eligible']);
        self::assertNull($eligibility[$release->getId()]['reason']);
    }

    public function testBuildEligibilityMapMarksPublishedReleaseAsIneligible(): void
    {
        [$application, $release] = $this->createApplicationAggregate('approved');
        $release->publish();
        $this->entityManager->flush();

        $eligibility = $this->applicationPublishEligibilityService->buildEligibilityMap($application);

        self::assertFalse($eligibility[$release->getId()]['eligible']);
        self::assertSame('Release is already published.', $eligibility[$release->getId()]['reason']);
    }

    /** @return array{0: Application, 1: ApplicationRelease} */
    private function createApplicationAggregate(?string $governanceState): array
    {
        $suffix = bin2hex(random_bytes(4));
        $application = new Application(
            'Eligibility Service Application ' . $suffix,
            'eligibility-service-' . $suffix,
            'applicating/eligibility-service-' . $suffix,
            'Applicating Labs',
            'Eligibility service summary'
        );
        $release = new ApplicationRelease(
            $application,
            '1.0.0',
            'stable',
            hash('sha256', 'eligibility-service-' . $suffix),
            'https://downloads.example.test/eligibility-service/' . $suffix . '/1.0.0.zip',
            'Eligibility service release notes'
        );
        $application->addRelease($release);

        $this->entityManager->persist($application);
        $this->entityManager->persist($release);

        if (null !== $governanceState) {
            $manifest = new ApplicationManifest(
                $application,
                '1.0.0',
                'io.applicating.eligibility.service.' . $suffix,
                ['catalog'],
                ['tenant:read'],
                ['bootstrap'],
                'default',
                $governanceState,
                ['identifier' => 'io.applicating.eligibility.service.' . $suffix]
            );
            $application->addManifest($manifest);
            $this->entityManager->persist($manifest);
        }

        $this->entityManager->flush();

        return [$application, $release];
    }
}
