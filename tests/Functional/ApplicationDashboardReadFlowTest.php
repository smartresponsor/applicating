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

final class ApplicationDashboardReadFlowTest extends WebTestCase
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

    public function testViewerSeesDashboardSummaryWithoutCreateAction(): void
    {
        $application = $this->createPublishedApplicationWithAssignment();

        $this->client->loginUser(new TestBrowserUser('viewer-test', ['ROLE_APPLICATION_VIEWER']));
        $this->client->request('GET', '/admin/applications');

        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();

        self::assertStringContainsString('Application Lifecycle Management', $content);
        self::assertStringContainsString('Applications', $content);
        self::assertStringContainsString('Published', $content);
        self::assertStringContainsString('Tenant Assignments', $content);
        self::assertStringContainsString('Billing Active', $content);
        self::assertStringContainsString($application->getName(), $content);
        self::assertStringNotContainsString('Create application', $content);
    }

    public function testManagerSeesDashboardCreateAction(): void
    {
        $this->createPublishedApplicationWithAssignment();

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $this->client->request('GET', '/admin/applications');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Create application', (string) $this->client->getResponse()->getContent());
    }

    public function testViewerSeesReportBoundaryCopyAndSummary(): void
    {
        $this->createPublishedApplicationWithAssignment();

        $this->client->loginUser(new TestBrowserUser('viewer-test', ['ROLE_APPLICATION_VIEWER']));
        $this->client->request('GET', '/admin/applications/report');

        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();

        self::assertStringContainsString('Application ecosystem report', $content);
        self::assertStringContainsString('application lifecycle responsibilities', $content);
        self::assertStringContainsString('Retail product counts and unrelated catalog metrics are not part of this component boundary.', $content);
        self::assertStringContainsString('Applications', $content);
        self::assertStringContainsString('Published', $content);
        self::assertStringContainsString('Tenant Assignments', $content);
        self::assertStringContainsString('Billing Active', $content);
    }

    private function createPublishedApplicationWithAssignment(): Application
    {
        $suffix = bin2hex(random_bytes(4));
        $application = new Application(
            'Dashboard Demo ' . $suffix,
            'dashboard-demo-' . $suffix,
            'applicating/dashboard-demo-' . $suffix,
            'Applicating Labs',
            'Dashboard listing summary'
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

        return $application;
    }
}
