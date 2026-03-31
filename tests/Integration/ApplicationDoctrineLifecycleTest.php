<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\Application;
use App\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ApplicationDoctrineLifecycleTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

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
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }

    public function testPrePersistInitializesCreatedAtAndUpdatedAt(): void
    {
        $application = new Application(
            'Lifecycle Timestamp Application',
            'lifecycle-timestamp-application',
            'applicating/lifecycle-timestamp-application',
            'Applicating Labs',
            'Lifecycle timestamp summary'
        );

        $this->entityManager->persist($application);
        $this->entityManager->flush();

        self::assertNotNull($application->getCreatedAt());
        self::assertNotNull($application->getUpdatedAt());
        self::assertEquals(
            $application->getCreatedAt()->format(DATE_ATOM),
            $application->getUpdatedAt()->format(DATE_ATOM)
        );
    }

    public function testPreUpdateRefreshesUpdatedAt(): void
    {
        $application = new Application(
            'Lifecycle Update Application',
            'lifecycle-update-application',
            'applicating/lifecycle-update-application',
            'Applicating Labs',
            'Lifecycle update summary'
        );

        $this->entityManager->persist($application);
        $this->entityManager->flush();
        $originalUpdatedAt = $application->getUpdatedAt();

        usleep(1000);
        $application->rename('Lifecycle Update Application Revised');
        $this->entityManager->flush();

        self::assertGreaterThan(
            $originalUpdatedAt->format('U.u'),
            $application->getUpdatedAt()->format('U.u')
        );
    }
}
