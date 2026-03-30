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

final class TenantApplicationRepositoryTest extends KernelTestCase
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

    public function testFindForTenantReturnsAssignmentsInDescendingAssignedOrder(): void
    {
        $this->createTenantAssignment('tenant-alpha', 'alpha-one');
        usleep(1000);
        $this->createTenantAssignment('tenant-alpha', 'alpha-two');
        $this->createTenantAssignment('tenant-beta', 'beta-one');

        $result = $this->tenantApplicationRepository->findForTenant('tenant-alpha');

        self::assertCount(2, $result);
        self::assertSame('alpha-two', $result[0]->getApplication()->getSlug());
        self::assertSame('alpha-one', $result[1]->getApplication()->getSlug());
    }

    public function testFindOneForTenantAndApplicationReturnsMatchingAssignment(): void
    {
        $this->createTenantAssignment('tenant-alpha', 'alpha-one');
        $this->createTenantAssignment('tenant-alpha', 'alpha-two');

        $assignment = $this->tenantApplicationRepository->findOneForTenantAndApplication('tenant-alpha', 'alpha-two');

        self::assertNotNull($assignment);
        self::assertSame('tenant-alpha', $assignment->getTenantKey());
        self::assertSame('alpha-two', $assignment->getApplication()->getSlug());
    }

    public function testFindOneForTenantAndApplicationReturnsNullWhenMissing(): void
    {
        $this->createTenantAssignment('tenant-alpha', 'alpha-one');

        self::assertNull(
            $this->tenantApplicationRepository->findOneForTenantAndApplication('tenant-alpha', 'missing-application')
        );
    }

    private function createTenantAssignment(string $tenantKey, string $slug): TenantApplication
    {
        $application = new Application(
            ucfirst($slug),
            $slug,
            'applicating/' . $slug,
            'Applicating Labs',
            'Tenant repository summary for ' . $slug
        );

        $tenantApplication = new TenantApplication(
            $application,
            $tenantKey,
            '1.0.0',
            true,
            true,
            ['scope' => 'tenant']
        );
        $application->addTenantApplication($tenantApplication);

        $this->entityManager->persist($application);
        $this->entityManager->persist($tenantApplication);
        $this->entityManager->flush();

        return $tenantApplication;
    }
}
