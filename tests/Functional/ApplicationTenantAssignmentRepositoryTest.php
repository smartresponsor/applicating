<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Functional;

use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Entity\ApplicationTenantAssignmentEntity;
use App\Applicating\RepositoryInterface\ApplicationTenantAssignmentRepositoryInterface;
use App\Applicating\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ApplicationTenantAssignmentRepositoryTest extends KernelTestCase
{
    public function testRepositoryQueriesAndCountsAssignments(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();
        DoctrineSchemaResetter::reset($entityManager);

        /** @var ApplicationTenantAssignmentRepositoryInterface $repository */
        $repository = $container->get(ApplicationTenantAssignmentRepositoryInterface::class);

        $application = new ApplicationEntity(
            'Repository Application',
            'repository-application',
            'applicating/repository-application',
            'Applicating Labs',
            'Repository summary.',
        );
        $entityManager->persist($application);
        $entityManager->flush();

        $enabled = new ApplicationTenantAssignmentEntity(
            $application,
            'tenant-one',
            '1.0.0',
            true,
            true,
            ['scope' => 'tenant'],
        );
        $disabled = new ApplicationTenantAssignmentEntity(
            $application,
            'tenant-two',
            '1.0.0',
            false,
            false,
            ['scope' => 'tenant'],
        );

        $repository->save($enabled);
        $repository->save($disabled);

        self::assertSame([$enabled], $repository->findForTenant('tenant-one'));
        self::assertSame($enabled, $repository->findOneForTenantAndApplication('tenant-one', 'repository-application'));
        self::assertSame($disabled, $repository->findOneForTenantAndApplicationEntity('tenant-two', $application));
        self::assertNull($repository->findOneForTenantAndApplication('missing', 'repository-application'));
        self::assertSame(2, $repository->countAllAssignments());
        self::assertSame(1, $repository->countEnabledAssignments());
        self::assertSame(1, $repository->countBillingActiveAssignments());
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }
}
