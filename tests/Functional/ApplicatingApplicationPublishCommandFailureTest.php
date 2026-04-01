<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Application;
use App\Entity\ApplicationManifest;
use App\Entity\ApplicationRelease;
use App\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Console\Application as ConsoleApplication;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

final class ApplicatingApplicationPublishCommandFailureTest extends KernelTestCase
{
    public function testCommandFailsGracefullyWithoutManifest(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();
        DoctrineSchemaResetter::reset($entityManager);

        $application = new Application(
            'Command Failure Application',
            'command-failure-application',
            'applicating/command-failure-application',
            'Applicating Labs',
            'Command failure summary'
        );
        $release = new ApplicationRelease(
            $application,
            '1.0.0',
            'stable',
            hash('sha256', 'command-failure'),
            'https://downloads.example.test/command-failure/1.0.0.zip',
            'Command failure release notes'
        );
        $application->addRelease($release);

        $entityManager->persist($application);
        $entityManager->persist($release);
        $entityManager->flush();

        /** @var KernelInterface $kernel */
        $kernel = self::$kernel;
        $console = new ConsoleApplication($kernel);
        $command = $console->find('applicating:application:publish');

        $tester = new CommandTester($command);
        $tester->execute([
            'slug' => 'command-failure-application',
        ]);

        self::assertSame(1, $tester->getStatusCode());
        self::assertStringContainsString('Application cannot be published without a manifest.', $tester->getDisplay());
    }

    public function testCommandFailsGracefullyWithoutApprovedManifest(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();
        DoctrineSchemaResetter::reset($entityManager);

        $application = new Application(
            'Command Review Required Application',
            'command-review-required-application',
            'applicating/command-review-required-application',
            'Applicating Labs',
            'Command review required summary'
        );
        $release = new ApplicationRelease(
            $application,
            '1.0.0',
            'stable',
            hash('sha256', 'command-review-required'),
            'https://downloads.example.test/command-review-required/1.0.0.zip',
            'Command review required release notes'
        );
        $application->addRelease($release);

        $manifest = new ApplicationManifest(
            $application,
            '1.0.0',
            'io.applicating.command.review.required',
            ['catalog'],
            ['tenant:read'],
            ['bootstrap'],
            'default',
            'review_required',
            ['identifier' => 'io.applicating.command.review.required']
        );
        $application->addManifest($manifest);

        $entityManager->persist($application);
        $entityManager->persist($release);
        $entityManager->persist($manifest);
        $entityManager->flush();

        /** @var KernelInterface $kernel */
        $kernel = self::$kernel;
        $console = new ConsoleApplication($kernel);
        $command = $console->find('applicating:application:publish');

        $tester = new CommandTester($command);
        $tester->execute([
            'slug' => 'command-review-required-application',
        ]);

        self::assertSame(1, $tester->getStatusCode());
        self::assertStringContainsString('Application cannot be published without an approved manifest.', $tester->getDisplay());
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }
}
