<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Application;
use App\Entity\ApplicationRelease;
use App\Tests\Support\DoctrineSchemaResetter;
use App\Tests\Support\Security\TestBrowserUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ApplicationRouteVoterAccessControlTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();

        /** @var ManagerRegistry $doctrine */
        $doctrine = static::getContainer()->get('doctrine');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();
        DoctrineSchemaResetter::reset($entityManager);
        $this->entityManager = $entityManager;
    }

    public function testViewerCannotAccessEditPage(): void
    {
        $application = $this->createApplication();

        $this->client->loginUser(new TestBrowserUser('viewer-test', ['ROLE_APPLICATION_VIEWER']));
        $this->client->request('GET', '/admin/applications/' . $application->getId() . '/edit');

        self::assertResponseStatusCodeSame(403);
    }

    public function testManagerCanAccessEditPage(): void
    {
        $application = $this->createApplication();

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $this->client->request('GET', '/admin/applications/' . $application->getId() . '/edit');

        self::assertResponseIsSuccessful();
    }

    public function testManagerCannotAssignDraftApplication(): void
    {
        $application = $this->createApplication();

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $this->client->request('POST', '/admin/applications/' . $application->getId() . '/assign');

        self::assertResponseStatusCodeSame(403);
    }

    public function testManagerCanReachAssignRouteForPublishedApplication(): void
    {
        $application = $this->createApplication();
        $application->publish();
        $this->entityManager->flush();

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $this->client->request('POST', '/admin/applications/' . $application->getId() . '/assign');

        self::assertResponseStatusCodeSame(302);
    }

    public function testManagerCannotPublishSuspendedApplication(): void
    {
        [$application, $release] = $this->createApplicationWithRelease();
        $application->suspend();
        $this->entityManager->flush();

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $this->client->request('POST', '/admin/applications/' . $application->getId() . '/publish/' . $release->getId());

        self::assertResponseStatusCodeSame(403);
    }

    public function testManagerCanReachPublishRouteForActiveApplication(): void
    {
        [$application, $release] = $this->createApplicationWithRelease();

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $this->client->request('POST', '/admin/applications/' . $application->getId() . '/publish/' . $release->getId());

        self::assertResponseStatusCodeSame(302);
    }

    private function createApplication(): Application
    {
        $suffix = bin2hex(random_bytes(4));
        $application = new Application(
            'Demo Application ' . $suffix,
            'demo-application-' . $suffix,
            'applicating/demo-application-' . $suffix,
            'Applicating Labs',
            'Demo listing summary'
        );

        $this->entityManager->persist($application);
        $this->entityManager->flush();

        return $application;
    }

    /** @return array{0: Application, 1: ApplicationRelease} */
    private function createApplicationWithRelease(): array
    {
        $application = $this->createApplication();
        $release = new ApplicationRelease(
            $application,
            '1.0.0',
            'stable',
            hash('sha256', 'demo-release'),
            'https://downloads.example.test/demo-application/1.0.0.zip',
            'Demo release notes'
        );
        $application->addRelease($release);

        $this->entityManager->persist($release);
        $this->entityManager->flush();

        return [$application, $release];
    }
}
