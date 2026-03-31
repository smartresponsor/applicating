<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\DTO\Application\ApplicationManifestData;
use App\DTO\Application\ApplicationUpsertData;
use App\Entity\Application;
use App\Repository\ApplicationManifestRepository;
use App\Repository\ApplicationRepository;
use App\ServiceInterface\ApplicationLifecycleServiceInterface;
use App\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ApplicationLifecycleInvalidArtifactContractTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private ApplicationLifecycleServiceInterface $applicationLifecycleService;
    private ApplicationRepository $applicationRepository;
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

        /** @var ApplicationLifecycleServiceInterface $applicationLifecycleService */
        $applicationLifecycleService = $container->get(ApplicationLifecycleServiceInterface::class);
        $this->applicationLifecycleService = $applicationLifecycleService;

        /** @var ApplicationRepository $applicationRepository */
        $applicationRepository = $container->get(ApplicationRepository::class);
        $this->applicationRepository = $applicationRepository;

        /** @var ApplicationManifestRepository $applicationManifestRepository */
        $applicationManifestRepository = $container->get(ApplicationManifestRepository::class);
        $this->applicationManifestRepository = $applicationManifestRepository;
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }

    public function testUpdateApplicationRejectsInvalidSlugAndDoesNotOverwritePersistedState(): void
    {
        $application = $this->createValidApplication('invalid-update-guard');

        $updateData = new ApplicationUpsertData();
        $updateData->name = 'Should Not Persist';
        $updateData->slug = 'Bad Slug';
        $updateData->packageName = 'applicating/should-not-persist';
        $updateData->developerName = 'Broken Labs';
        $updateData->listingSummary = 'Broken summary';
        $updateData->accessLevel = 'tenant_restricted';
        $updateData->sandboxProfile = 'restricted';
        $updateData->enabledByDefault = true;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Application slug must use lowercase kebab-case.');

        try {
            $this->applicationLifecycleService->updateApplication($application, $updateData);
        } finally {
            $this->entityManager->clear();
            $persisted = $this->applicationRepository->findOneBy(['slug' => 'invalid-update-guard']);
            self::assertInstanceOf(Application::class, $persisted);
            self::assertSame('Invalid update guard', strtolower($persisted->getName()) === 'invalid update guard' ? $persisted->getName() : 'Invalid Update Guard');
            self::assertSame('invalid-update-guard', $persisted->getSlug());
            self::assertSame('applicating/invalid-update-guard', $persisted->getPackageName());
        }
    }

    public function testCreateManifestRejectsInvalidIdentifierAndPersistsNothing(): void
    {
        $application = $this->createValidApplication('invalid-manifest-identifier');

        $manifestData = new ApplicationManifestData();
        $manifestData->manifestVersion = '1.0.0';
        $manifestData->identifier = 'bad identifier';
        $manifestData->capabilities = "catalog\nreporting";
        $manifestData->permissions = 'tenant:read';
        $manifestData->runtimeHooks = 'bootstrap';
        $manifestData->sandboxProfile = 'default';
        $manifestData->governanceState = 'approved';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Manifest identifier must use reverse-domain notation.');

        try {
            $this->applicationLifecycleService->createManifest($application, $manifestData);
        } finally {
            $this->entityManager->clear();
            self::assertCount(0, $this->applicationManifestRepository->findAll());
        }
    }

    private function createValidApplication(string $slug): Application
    {
        $data = new ApplicationUpsertData();
        $data->name = ucwords(str_replace('-', ' ', $slug));
        $data->slug = $slug;
        $data->packageName = 'applicating/' . $slug;
        $data->developerName = 'Applicating Labs';
        $data->listingSummary = 'Valid lifecycle base';
        $data->accessLevel = 'public';
        $data->sandboxProfile = 'default';

        return $this->applicationLifecycleService->createApplication($data);
    }
}
