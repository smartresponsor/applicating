<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Support\DoctrineSchemaResetter;
use App\Tests\Support\Security\TestBrowserUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ApplicationManagementAccessControlTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();

        /** @var ManagerRegistry $doctrine */
        $doctrine = static::getContainer()->get('doctrine');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();
        DoctrineSchemaResetter::reset($entityManager);
    }

    public function testViewerCanAccessAdminReportPage(): void
    {
        $this->client->loginUser(new TestBrowserUser('viewer-test', ['ROLE_APPLICATION_VIEWER']));
        $this->client->request('GET', '/admin/applications/report');

        self::assertResponseIsSuccessful();
    }

    public function testViewerCannotAccessApplicationCreationPage(): void
    {
        $this->client->loginUser(new TestBrowserUser('viewer-test', ['ROLE_APPLICATION_VIEWER']));
        $this->client->request('GET', '/admin/applications/new');

        self::assertResponseStatusCodeSame(403);
    }

    public function testManagerCanAccessApplicationCreationPage(): void
    {
        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $this->client->request('GET', '/admin/applications/new');

        self::assertResponseIsSuccessful();
    }

    public function testViewerCannotAccessAdminApiReport(): void
    {
        $this->client->loginUser(new TestBrowserUser('viewer-test', ['ROLE_APPLICATION_VIEWER']));
        $this->client->request('GET', '/api/admin/applications/report');

        self::assertResponseStatusCodeSame(403);
    }

    public function testManagerCanAccessAdminApiReport(): void
    {
        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $this->client->request('GET', '/api/admin/applications/report');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('application/json', (string) $this->client->getResponse()->headers->get('content-type'));
        self::assertStringContainsString('summary', (string) $this->client->getResponse()->getContent());
    }
}
