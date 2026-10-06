<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Functional;

use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Console\Application as ConsoleApplication;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

final class ApplicatingApplicationPublishCommandGuardTest extends KernelTestCase
{
    public function testCommandFailsWhenApplicationDoesNotExist(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();
        DoctrineSchemaResetter::reset($entityManager);

        $tester = $this->publishCommandTester();
        $tester->execute(['slug' => 'missing-application']);

        self::assertSame(1, $tester->getStatusCode());
        self::assertStringContainsString('Application not found.', $tester->getDisplay());
    }

    public function testCommandFailsWhenApplicationHasNoRelease(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();
        DoctrineSchemaResetter::reset($entityManager);

        $entityManager->persist(new ApplicationEntity(
            'Command No Release Application',
            'command-no-release-application',
            'applicating/command-no-release-application',
            'Applicating Labs',
            'Command no release summary.',
        ));
        $entityManager->flush();

        $tester = $this->publishCommandTester();
        $tester->execute(['slug' => 'command-no-release-application']);

        self::assertSame(1, $tester->getStatusCode());
        self::assertStringContainsString('Application has no release to publish.', $tester->getDisplay());
    }

    public function testCommandRejectsEmptySlug(): void
    {
        self::bootKernel();

        $tester = $this->publishCommandTester();
        $tester->execute(['slug' => '']);

        self::assertSame(1, $tester->getStatusCode());
        self::assertStringContainsString('Application slug must be a non-empty string.', $tester->getDisplay());
    }

    private function publishCommandTester(): CommandTester
    {
        /** @var KernelInterface $kernel */
        $kernel = self::$kernel;
        $console = new ConsoleApplication($kernel);

        return new CommandTester($console->find('applicating:application:publish'));
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }
}
