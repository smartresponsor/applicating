<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\DTO\Application\ApplicationUpsertData;
use App\Entity\Application;
use App\Repository\ApplicationRepository;
use App\ServiceInterface\ApplicationLifecycleServiceInterface;
use App\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ApplicationLifecycleUpdateContractTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private ApplicationLifecycleServiceInterface $applicationLifecycleService;
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

        /** @var ApplicationLifecycleServiceInterface $applicationLifecycleService */
        $applicationLifecycleService = $container->get(ApplicationLifecycleServiceInterface::class);
        $this->applicationLifecycleService = $applicationLifecycleService;

        /** @var ApplicationRepository $applicationRepository */
        $applicationRepository = $container->get(ApplicationRepository::class);
        $this->applicationRepository = $applicationRepository;
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }

    public function testUpdateApplicationPersistsFullLifecycleEditableContract(): void
    {
        $createData = new ApplicationUpsertData();
        $createData->name = 'Initial Lifecycle Application';
        $createData->slug = 'initial-lifecycle-application';
        $createData->packageName = 'applicating/initial-lifecycle-application';
        $createData->developerName = 'Applicating Labs';
        $createData->listingSummary = 'Initial lifecycle summary';
        $createData->accessLevel = 'public';
        $createData->billingCode = 'APP-INITIAL';
        $createData->sandboxProfile = 'default';
        $createData->enabledByDefault = false;

        $application = $this->applicationLifecycleService->createApplication($createData);

        $updateData = new ApplicationUpsertData();
        $updateData->name = 'Updated Lifecycle Application';
        $updateData->slug = 'updated-lifecycle-application';
        $updateData->packageName = 'applicating/updated-lifecycle-application';
        $updateData->developerName = 'Updated Labs';
        $updateData->listingSummary = 'Updated lifecycle summary';
        $updateData->accessLevel = 'tenant_restricted';
        $updateData->billingCode = '';
        $updateData->sandboxProfile = 'restricted';
        $updateData->enabledByDefault = true;

        $updated = $this->applicationLifecycleService->updateApplication($application, $updateData);

        $this->entityManager->clear();
        $persisted = $this->applicationRepository->find($updated->getId());
        self::assertInstanceOf(Application::class, $persisted);
        self::assertSame('Updated Lifecycle Application', $persisted->getName());
        self::assertSame('updated-lifecycle-application', $persisted->getSlug());
        self::assertSame('applicating/updated-lifecycle-application', $persisted->getPackageName());
        self::assertSame('Updated Labs', $persisted->getDeveloperName());
        self::assertSame('Updated lifecycle summary', $persisted->getListingSummary());
        self::assertSame('tenant_restricted', $persisted->getAccessLevel()->value);
        self::assertNull($persisted->getBillingCode());
        self::assertSame('restricted', $persisted->getSandboxProfile());
        self::assertTrue($persisted->isEnabledByDefault());
    }
}
