<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Support\DoctrineSchemaResetter;
use App\Tests\Support\Security\TestBrowserUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ApplicationAccessControlTest extends WebTestCase
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

    public function testAnonymousUserIsRedirectedFromAdminIndex(): void
    {
        $this->client->request('GET', '/admin/applications');

        self::assertResponseRedirects('/login');
    }

    public function testViewerCanAccessAdminIndex(): void
    {
        $this->client->loginUser(new TestBrowserUser('viewer-test', ['ROLE_APPLICATION_VIEWER']));
        $this->client->request('GET', '/admin/applications');

        self::assertResponseIsSuccessful();
    }

    public function testViewerCannotAccessAdminApiIndex(): void
    {
        $this->client->loginUser(new TestBrowserUser('viewer-test', ['ROLE_APPLICATION_VIEWER']));
        $this->client->request('GET', '/api/admin/applications');

        self::assertResponseStatusCodeSame(403);
    }

    public function testManagerCanAccessAdminApiIndex(): void
    {
        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $this->client->request('GET', '/api/admin/applications');

        self::assertResponseIsSuccessful();
        self::assertResponseFormatSame('json');
        self::assertStringContainsString('applications', (string) $this->client->getResponse()->getContent());
    }
}
