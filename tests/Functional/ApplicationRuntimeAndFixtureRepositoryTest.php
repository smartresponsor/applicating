<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Functional;

use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Entity\ApplicationManifestEntity;
use App\Applicating\Entity\ApplicationReleaseEntity;
use App\Applicating\Entity\ApplicationRuntimeAssignmentEntity;
use App\Applicating\Entity\ApplicationTenantAssignmentEntity;
use App\Applicating\Enum\ApplicationRuntimeMode;
use App\Applicating\Repository\ApplicationFixtureRepository;
use App\Applicating\RepositoryInterface\ApplicationRuntimeAssignmentRepositoryInterface;
use App\Applicating\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ApplicationRuntimeAndFixtureRepositoryTest extends KernelTestCase
{
    public function testRuntimeRepositoryAndFixtureReset(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();
        DoctrineSchemaResetter::reset($entityManager);

        /** @var ApplicationRuntimeAssignmentRepositoryInterface $runtimeRepository */
        $runtimeRepository = $container->get(ApplicationRuntimeAssignmentRepositoryInterface::class);

        $application = new ApplicationEntity(
            'Runtime Repository Application',
            'runtime-repository-application',
            'applicating/runtime-repository-application',
            'Applicating Labs',
            'Runtime repository coverage.',
        );
        $release = new ApplicationReleaseEntity(
            $application,
            '2.0.0',
            'stable',
            hash('sha256', 'runtime-repository'),
            'https://downloads.example.test/runtime-repository/2.0.0.zip',
            'Runtime repository release notes.',
        );
        $manifest = new ApplicationManifestEntity(
            $application,
            '1.0.0',
            'io.applicating.runtime.repository',
            ['listing'],
            ['tenant:read'],
            ['bootstrap'],
            'default',
            'approved',
            ['identifier' => 'io.applicating.runtime.repository'],
        );
        $tenantAssignment = new ApplicationTenantAssignmentEntity(
            $application,
            'tenant-runtime',
            '2.0.0',
            true,
            true,
            [],
        );
        $runtimeAssignment = new ApplicationRuntimeAssignmentEntity(
            $application,
            ' production ',
            ApplicationRuntimeMode::CustomDomain,
        );

        $application->addRelease($release);
        $application->addManifest($manifest);
        $entityManager->persist($application);
        $entityManager->persist($release);
        $entityManager->persist($manifest);
        $entityManager->persist($tenantAssignment);
        $runtimeRepository->save($runtimeAssignment);

        self::assertSame('production', $runtimeAssignment->getEnvironment());
        self::assertSame(
            $runtimeAssignment->getId(),
            $runtimeRepository->findOneForApplicationAndEnvironment(' runtime-repository-application ', ' production ')?->getId(),
        );
        self::assertSame(
            $runtimeAssignment->getId(),
            $runtimeRepository->findOneForApplicationEntityAndEnvironment($application, ' production ')?->getId(),
        );
        self::assertNull($runtimeRepository->findOneForApplicationAndEnvironment('missing', 'production'));

        $fixtureRepository = new ApplicationFixtureRepository($entityManager);
        $fixtureRepository->resetDemoData();

        self::assertSame(0, $entityManager->getRepository(ApplicationEntity::class)->count([]));
        self::assertSame(0, $entityManager->getRepository(ApplicationReleaseEntity::class)->count([]));
        self::assertSame(0, $entityManager->getRepository(ApplicationManifestEntity::class)->count([]));
        self::assertSame(0, $entityManager->getRepository(ApplicationTenantAssignmentEntity::class)->count([]));
        self::assertSame(0, $entityManager->getRepository(ApplicationRuntimeAssignmentEntity::class)->count([]));
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }
}
