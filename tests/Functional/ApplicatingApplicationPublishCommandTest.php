<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\DTO\Application\ApplicationManifestData;
use App\DTO\Application\ApplicationReleaseData;
use App\DTO\Application\ApplicationUpsertData;
use App\Entity\Application;
use App\Repository\ApplicationRepository;
use App\ServiceInterface\ApplicationLifecycleServiceInterface;
use App\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Console\Application as ConsoleApplication;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

final class ApplicatingApplicationPublishCommandTest extends KernelTestCase
{
    public function testCommandPublishesApplicationWithLatestRelease(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();
        DoctrineSchemaResetter::reset($entityManager);

        /** @var ApplicationLifecycleServiceInterface $service */
        $service = $container->get(ApplicationLifecycleServiceInterface::class);
        $applicationData = new ApplicationUpsertData();
        $applicationData->name = 'Publishable Application';
        $applicationData->slug = 'publishable-application';
        $applicationData->packageName = 'applicating/publishable-application';
        $applicationData->developerName = 'Applicating Labs';
        $applicationData->listingSummary = 'Publish command target';
        $application = $service->createApplication($applicationData);

        $releaseData = new ApplicationReleaseData();
        $releaseData->version = '2.1.0';
        $releaseData->checksum = hash('sha256', 'publishable-application');
        $releaseData->downloadUrl = 'https://downloads.example.test/publishable-application/2.1.0.zip';
        $releaseData->releaseNotes = 'Publish command release';
        $release = $service->createRelease($application, $releaseData);

        $manifestData = new ApplicationManifestData();
        $manifestData->identifier = 'io.applicating.publishable.application';
        $manifestData->capabilities = "catalog\nreporting";
        $manifestData->permissions = 'tenant:read';
        $manifestData->runtimeHooks = 'bootstrap';
        $manifestData->governanceState = 'approved';
        $service->createManifest($application, $manifestData);

        /** @var KernelInterface $kernel */
        $kernel = self::$kernel;
        $console = new ConsoleApplication($kernel);
        $command = $console->find('applicating:application:publish');

        $tester = new CommandTester($command);
        $tester->execute([
            'slug' => 'publishable-application',
        ]);

        self::assertSame(0, $tester->getStatusCode());
        self::assertStringContainsString('Published publishable-application with release 2.1.0.', $tester->getDisplay());

        /** @var ApplicationRepository $repository */
        $repository = $container->get(ApplicationRepository::class);
        $persistedApplication = $repository->findOneBy(['slug' => 'publishable-application']);
        self::assertInstanceOf(Application::class, $persistedApplication);
        self::assertSame('published', $persistedApplication->getPublicationState()->value);
        self::assertSame('published', $persistedApplication->getReleases()->first()->getPublicationState()->value);
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }
}
