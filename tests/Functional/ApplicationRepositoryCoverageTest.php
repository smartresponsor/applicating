<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Functional;

use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Entity\ApplicationManifestEntity;
use App\Applicating\Entity\ApplicationReleaseEntity;
use App\Applicating\RepositoryInterface\ApplicationManifestRepositoryInterface;
use App\Applicating\RepositoryInterface\ApplicationReleaseRepositoryInterface;
use App\Applicating\RepositoryInterface\ApplicationRepositoryInterface;
use App\Applicating\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ApplicationRepositoryCoverageTest extends KernelTestCase
{
    public function testApplicationReleaseAndManifestRepositories(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();
        DoctrineSchemaResetter::reset($entityManager);

        /** @var ApplicationRepositoryInterface $applicationRepository */
        $applicationRepository = $container->get(ApplicationRepositoryInterface::class);
        /** @var ApplicationReleaseRepositoryInterface $releaseRepository */
        $releaseRepository = $container->get(ApplicationReleaseRepositoryInterface::class);
        /** @var ApplicationManifestRepositoryInterface $manifestRepository */
        $manifestRepository = $container->get(ApplicationManifestRepositoryInterface::class);

        $draft = new ApplicationEntity(
            'Draft Application',
            'draft-application',
            'applicating/draft-application',
            'Applicating Labs',
            'Draft summary.',
        );
        $published = new ApplicationEntity(
            'Published Application',
            'published-application',
            'applicating/published-application',
            'Applicating Labs',
            'Published summary.',
        );
        $published->publish();

        $applicationRepository->save($draft);
        $applicationRepository->save($published);

        self::assertSame($draft->getId(), $applicationRepository->findOneBySlug('draft-application')?->getId());
        self::assertNull($applicationRepository->findOneBySlug('missing-application'));
        self::assertSame(2, $applicationRepository->countAllApplications());
        self::assertSame(1, $applicationRepository->countPublished());
        self::assertCount(2, $applicationRepository->findOrderedForAdmin());

        $release = new ApplicationReleaseEntity(
            $draft,
            '1.2.3',
            'stable',
            hash('sha256', 'repository-release'),
            'https://downloads.example.test/repository/1.2.3.zip',
            'Repository release notes.',
        );
        $releaseRepository->save($release);
        self::assertSame(
            $release->getId(),
            $releaseRepository->findOneForApplicationAndVersion($draft, '1.2.3')?->getId(),
        );
        self::assertNull($releaseRepository->findOneForApplicationAndVersion($draft, '9.9.9'));

        $manifest = new ApplicationManifestEntity(
            $draft,
            '1.0.0',
            'io.applicating.repository',
            ['catalog.read'],
            ['tenant:read'],
            ['boot'],
            'default',
            'approved',
            ['identifier' => 'io.applicating.repository'],
        );
        $manifestRepository->save($manifest);
        self::assertSame(
            $manifest->getId(),
            $manifestRepository->findOneForApplicationAndIdentifier($draft, 'io.applicating.repository')?->getId(),
        );
        self::assertNull($manifestRepository->findOneForApplicationAndIdentifier($draft, 'io.applicating.missing'));
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }
}
