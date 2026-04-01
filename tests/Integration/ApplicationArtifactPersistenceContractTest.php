<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\Application;
use App\Entity\ApplicationManifest;
use App\Entity\ApplicationRelease;
use App\Repository\ApplicationManifestRepository;
use App\Repository\ApplicationReleaseRepository;
use App\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ApplicationArtifactPersistenceContractTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private ApplicationReleaseRepository $applicationReleaseRepository;
    private ApplicationManifestRepository $applicationManifestRepository;

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

        /** @var ApplicationReleaseRepository $applicationReleaseRepository */
        $applicationReleaseRepository = $container->get(ApplicationReleaseRepository::class);
        $this->applicationReleaseRepository = $applicationReleaseRepository;

        /** @var ApplicationManifestRepository $applicationManifestRepository */
        $applicationManifestRepository = $container->get(ApplicationManifestRepository::class);
        $this->applicationManifestRepository = $applicationManifestRepository;
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }

    public function testPersistedReleaseKeepsMetadataAndPublicationTimestamp(): void
    {
        $application = new Application(
            'Artifact Persistence Application',
            'artifact-persistence-application',
            'applicating/artifact-persistence-application',
            'Applicating Labs',
            'Artifact persistence summary'
        );

        $release = new ApplicationRelease(
            $application,
            '2.3.4',
            'stable',
            hash('sha256', 'artifact-release'),
            'https://downloads.example.test/artifact-release/2.3.4.zip',
            'Artifact release notes'
        );
        $application->addRelease($release);
        $release->publish();

        $this->entityManager->persist($application);
        $this->entityManager->persist($release);
        $this->entityManager->flush();
        $releaseId = $release->getId();

        $this->entityManager->clear();

        $persisted = $this->applicationReleaseRepository->find($releaseId);
        self::assertInstanceOf(ApplicationRelease::class, $persisted);
        self::assertSame('2.3.4', $persisted->getVersion());
        self::assertSame('stable', $persisted->getChannel());
        self::assertSame('https://downloads.example.test/artifact-release/2.3.4.zip', $persisted->getDownloadUrl());
        self::assertSame('Artifact release notes', $persisted->getReleaseNotes());
        self::assertSame('published', $persisted->getPublicationState()->value);
        self::assertNotNull($persisted->getCreatedAt());
        self::assertNotNull($persisted->getPublishedAt());
        self::assertSame('artifact-persistence-application', $persisted->getApplication()->getSlug());
    }

    public function testPersistedManifestKeepsArraysAndRawPayload(): void
    {
        $application = new Application(
            'Manifest Persistence Application',
            'manifest-persistence-application',
            'applicating/manifest-persistence-application',
            'Applicating Labs',
            'Manifest persistence summary'
        );

        $manifest = new ApplicationManifest(
            $application,
            '1.0.0',
            'io.applicating.manifest.persistence',
            ['catalog', 'reporting'],
            ['tenant:read', 'tenant:write'],
            ['bootstrap', 'shutdown'],
            'restricted',
            'approved',
            [
                'identifier' => 'io.applicating.manifest.persistence',
                'capabilities' => ['catalog', 'reporting'],
                'sandboxProfile' => 'restricted',
            ]
        );
        $application->addManifest($manifest);

        $this->entityManager->persist($application);
        $this->entityManager->persist($manifest);
        $this->entityManager->flush();
        $manifestId = $manifest->getId();

        $this->entityManager->clear();

        $persisted = $this->applicationManifestRepository->find($manifestId);
        self::assertInstanceOf(ApplicationManifest::class, $persisted);
        self::assertSame('1.0.0', $persisted->getManifestVersion());
        self::assertSame('io.applicating.manifest.persistence', $persisted->getIdentifier());
        self::assertSame(['catalog', 'reporting'], $persisted->getCapabilities());
        self::assertSame(['tenant:read', 'tenant:write'], $persisted->getPermissions());
        self::assertSame(['bootstrap', 'shutdown'], $persisted->getRuntimeHooks());
        self::assertSame('restricted', $persisted->getSandboxProfile());
        self::assertSame('approved', $persisted->getGovernanceState());
        self::assertSame(
            [
                'identifier' => 'io.applicating.manifest.persistence',
                'capabilities' => ['catalog', 'reporting'],
                'sandboxProfile' => 'restricted',
            ],
            $persisted->getRawManifest()
        );
        self::assertNotNull($persisted->getCreatedAt());
        self::assertSame('manifest-persistence-application', $persisted->getApplication()->getSlug());
    }
}
