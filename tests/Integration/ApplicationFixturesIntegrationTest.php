<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\DataFixtures\ApplicationFixtures;
use App\Repository\ApplicationRepository;
use App\Repository\TenantApplicationRepository;
use App\ServiceInterface\ApplicationLifecycleServiceInterface;
use App\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ApplicationFixturesIntegrationTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private ApplicationRepository $applicationRepository;
    private TenantApplicationRepository $tenantApplicationRepository;
    private ApplicationLifecycleServiceInterface $applicationLifecycleService;

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

        /** @var TenantApplicationRepository $tenantApplicationRepository */
        $tenantApplicationRepository = $container->get(TenantApplicationRepository::class);
        $this->tenantApplicationRepository = $tenantApplicationRepository;

        /** @var ApplicationLifecycleServiceInterface $applicationLifecycleService */
        $applicationLifecycleService = $container->get(ApplicationLifecycleServiceInterface::class);
        $this->applicationLifecycleService = $applicationLifecycleService;
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }

    public function testFixturesLoadExpectedApplicationLandscape(): void
    {
        $fixtures = new ApplicationFixtures($this->applicationLifecycleService);
        $fixtures->load($this->entityManager);

        $applications = $this->applicationRepository->findOrderedForAdmin();
        self::assertCount(6, $applications);
        self::assertSame(4, $this->applicationRepository->countPublished());
        self::assertCount(6, $this->tenantApplicationRepository->findAll());

        $publishedWithReviewRequiredGovernance = 0;
        foreach ($applications as $application) {
            if ($application->getPublicationState()->value !== 'published') {
                continue;
            }

            foreach ($application->getManifests() as $manifest) {
                if ($manifest->getGovernanceState() === 'review_required') {
                    ++$publishedWithReviewRequiredGovernance;
                    break;
                }
            }
        }

        self::assertGreaterThanOrEqual(
            1,
            $publishedWithReviewRequiredGovernance,
            'Current fixtures should expose the governance/publish risk until the publish-guard wave aligns fixture publication rules.'
        );
    }
}
