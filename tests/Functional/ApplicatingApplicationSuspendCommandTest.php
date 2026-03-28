<?php

declare(strict_types=1);

namespace App\Tests\Functional;

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

final class ApplicatingApplicationSuspendCommandTest extends KernelTestCase
{
    public function testCommandSuspendsPublishedApplication(): void
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
        $applicationData->name = 'Governed Application';
        $applicationData->slug = 'governed-application';
        $applicationData->packageName = 'applicating/governed-application';
        $applicationData->developerName = 'Applicating Labs';
        $applicationData->listingSummary = 'Publication governance target';
        $application = $service->createApplication($applicationData);

        $releaseData = new ApplicationReleaseData();
        $releaseData->version = '2.0.0';
        $releaseData->checksum = hash('sha256', 'governed-application');
        $releaseData->downloadUrl = 'https://downloads.example.test/governed-application/2.0.0.zip';
        $releaseData->releaseNotes = 'Governance release';
        $release = $service->createRelease($application, $releaseData);
        $service->publishApplication($application, $release);

        /** @var KernelInterface $kernel */
        $kernel = self::$kernel;
        $console = new ConsoleApplication($kernel);
        $command = $console->find('applicating:application:suspend');

        $tester = new CommandTester($command);
        $tester->execute([
            'slug' => 'governed-application',
        ]);

        self::assertSame(0, $tester->getStatusCode());
        self::assertStringContainsString('Suspended governed-application.', $tester->getDisplay());

        /** @var ApplicationRepository $repository */
        $repository = $container->get(ApplicationRepository::class);
        $persistedApplication = $repository->findOneBy(['slug' => 'governed-application']);
        self::assertInstanceOf(Application::class, $persistedApplication);
        self::assertSame('suspended', $persistedApplication->getPublicationState()->value);
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }
}
