<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\Application;
use App\Entity\ApplicationRelease;
use App\Entity\TenantApplication;
use App\Repository\ApplicationRepository;
use App\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ApplicationRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private ApplicationRepository $applicationRepository;

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

        /** @var ApplicationRepository $applicationRepository */
        $applicationRepository = $container->get(ApplicationRepository::class);
        $this->applicationRepository = $applicationRepository;
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }

    public function testFindOrderedForAdminReturnsApplicationsWithJoinedRelations(): void
    {
        $older = $this->createApplicationAggregate('older-app', false);
        usleep(1000);
        $newer = $this->createApplicationAggregate('newer-app', true);

        $result = $this->applicationRepository->findOrderedForAdmin();

        self::assertCount(2, $result);
        self::assertSame('newer-app', $result[0]->getSlug());
        self::assertSame('older-app', $result[1]->getSlug());
        self::assertCount(1, $result[0]->getReleases());
        self::assertCount(1, $result[0]->getTenantApplications());
        self::assertCount(1, $result[1]->getReleases());
        self::assertCount(1, $result[1]->getTenantApplications());
    }

    public function testCountPublishedCountsOnlyPublishedApplications(): void
    {
        $this->createApplicationAggregate('draft-app', false);
        $this->createApplicationAggregate('published-app', true);

        self::assertSame(1, $this->applicationRepository->countPublished());
    }

    private function createApplicationAggregate(string $slug, bool $published): Application
    {
        $application = new Application(
            ucfirst($slug),
            $slug,
            'applicating/' . $slug,
            'Applicating Labs',
            'Repository summary for ' . $slug
        );

        $release = new ApplicationRelease(
            $application,
            '1.0.0',
            'stable',
            hash('sha256', $slug),
            'https://downloads.example.test/' . $slug . '/1.0.0.zip',
            'Repository release notes'
        );
        $application->addRelease($release);

        $tenantApplication = new TenantApplication(
            $application,
            'tenant-' . $slug,
            '1.0.0',
            true,
            true,
            ['scope' => 'tenant']
        );
        $application->addTenantApplication($tenantApplication);

        if ($published) {
            $application->publish();
            $release->publish();
        }

        $this->entityManager->persist($application);
        $this->entityManager->persist($release);
        $this->entityManager->persist($tenantApplication);
        $this->entityManager->flush();

        return $application;
    }
}
