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

final class ApplicatingApplicationPublishCommandTest extends KernelTestCase
{
    public function testCommandPublishesApplicationWithApprovedManifest(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();
        DoctrineSchemaResetter::reset($entityManager);

        $application = new Application(
            'Command Publish Application',
            'command-publish-application',
            'applicating/command-publish-application',
            'Applicating Labs',
            'Command publish summary'
        );
        $release = new ApplicationRelease(
            $application,
            '1.0.0',
            'stable',
            hash('sha256', 'command-publish'),
            'https://downloads.example.test/command-publish/1.0.0.zip',
            'Command publish release notes'
        );
        $application->addRelease($release);

        $manifest = new ApplicationManifest(
            $application,
            '1.0.0',
            'io.applicating.command.publish',
            ['listing'],
            ['tenant:read'],
            ['bootstrap'],
            'default',
            'approved',
            ['identifier' => 'io.applicating.command.publish']
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
            'slug' => 'command-publish-application',
        ]);

        self::assertSame(0, $tester->getStatusCode());
        self::assertStringContainsString('published', strtolower($tester->getDisplay()));
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }
}
