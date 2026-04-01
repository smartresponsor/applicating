<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Application;
use App\Entity\TenantApplication;
use App\Tests\Support\DoctrineSchemaResetter;
use App\Tests\Support\Security\TestBrowserUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class TenantApplicationToggleAccessControlTest extends WebTestCase
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

    public function testViewerCannotToggleTenantApplication(): void
    {
        $tenantApplication = $this->createTenantApplication();

        $this->client->loginUser(new TestBrowserUser('viewer-test', ['ROLE_APPLICATION_VIEWER']));
        $this->client->request('POST', '/admin/applications/tenant-assignment/' . $tenantApplication->getId() . '/toggle', [
            'enabled' => '0',
        ]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testManagerCanToggleTenantApplication(): void
    {
        $tenantApplication = $this->createTenantApplication();

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $this->client->request('POST', '/admin/applications/tenant-assignment/' . $tenantApplication->getId() . '/toggle', [
            'enabled' => '0',
        ]);

        self::assertResponseStatusCodeSame(302);
    }

    private function createTenantApplication(): TenantApplication
    {
        $suffix = bin2hex(random_bytes(4));
        $application = new Application(
            'Demo Application ' . $suffix,
            'demo-application-' . $suffix,
            'applicating/demo-application-' . $suffix,
            'Applicating Labs',
            'Demo listing summary'
        );
        $application->publish();

        $tenantApplication = new TenantApplication(
            $application,
            'tenant-' . $suffix,
            '1.0.0',
            true,
            true,
            ['scope' => 'tenant']
        );
        $application->addTenantApplication($tenantApplication);

        $this->entityManager->persist($application);
        $this->entityManager->persist($tenantApplication);
        $this->entityManager->flush();

        return $tenantApplication;
    }
}
