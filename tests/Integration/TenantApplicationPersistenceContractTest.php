<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\Application;
use App\Entity\TenantApplication;
use App\Repository\TenantApplicationRepository;
use App\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class TenantApplicationPersistenceContractTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private TenantApplicationRepository $tenantApplicationRepository;

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

        /** @var TenantApplicationRepository $tenantApplicationRepository */
        $tenantApplicationRepository = $container->get(TenantApplicationRepository::class);
        $this->tenantApplicationRepository = $tenantApplicationRepository;
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }

    public function testPersistedTenantApplicationKeepsTimestampsAndJsonState(): void
    {
        $application = new Application(
            'Tenant Persistence Application',
            'tenant-persistence-application',
            'applicating/tenant-persistence-application',
            'Applicating Labs',
            'Tenant persistence summary'
        );

        $tenantApplication = new TenantApplication(
            $application,
            'tenant-persistence',
            '1.0.0',
            true,
            true,
            ['scope' => 'tenant', 'roles' => ['ROLE_APPLICATION_USER']]
        );
        $tenantApplication->setDiagnostics([
            'status' => 'ok',
            'checks' => ['manifest_present' => false],
        ]);
        $application->addTenantApplication($tenantApplication);

        $this->entityManager->persist($application);
        $this->entityManager->persist($tenantApplication);
        $this->entityManager->flush();
        $tenantApplicationId = $tenantApplication->getId();

        $this->entityManager->clear();

        $persisted = $this->tenantApplicationRepository->find($tenantApplicationId);
        self::assertInstanceOf(TenantApplication::class, $persisted);
        self::assertSame('tenant-persistence', $persisted->getTenantKey());
        self::assertSame('1.0.0', $persisted->getInstalledVersion());
        self::assertTrue($persisted->isEnabled());
        self::assertTrue($persisted->isBillingActive());
        self::assertSame('installed', $persisted->getInstallationState()->value);
        self::assertSame(
            ['scope' => 'tenant', 'roles' => ['ROLE_APPLICATION_USER']],
            $persisted->getAccessPolicy()
        );
        self::assertSame(
            ['status' => 'ok', 'checks' => ['manifest_present' => false]],
            $persisted->getDiagnostics()
        );
        self::assertNotNull($persisted->getAssignedAt());
        self::assertNotNull($persisted->getInstalledAt());
        self::assertNotNull($persisted->getLastCheckedAt());
        self::assertSame('tenant-persistence-application', $persisted->getApplication()->getSlug());
    }
}
