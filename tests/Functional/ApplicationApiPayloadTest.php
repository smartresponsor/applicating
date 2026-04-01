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

final class ApplicationApiPayloadTest extends WebTestCase
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

    public function testAdminApiIndexReturnsApplicationPayload(): void
    {
        $application = $this->createPublishedApplicationWithAssignment();

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $this->client->request('GET', '/api/admin/applications');

        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('applications', $payload);
        self::assertCount(1, $payload['applications']);
        self::assertSame($application->getName(), $payload['applications'][0]['name']);
        self::assertSame('published', $payload['applications'][0]['publicationState']);
        self::assertSame(1, $payload['applications'][0]['tenantAssignmentCount']);
    }

    public function testAdminApiReportReturnsSummaryPayload(): void
    {
        $this->createPublishedApplicationWithAssignment();

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $this->client->request('GET', '/api/admin/applications/report');

        self::assertResponseIsSuccessful();
        $payload = json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('summary', $payload);
        self::assertSame(1, $payload['summary']['applicationsTotal']);
        self::assertSame(1, $payload['summary']['applicationsPublished']);
        self::assertSame(1, $payload['summary']['tenantAssignmentsTotal']);
        self::assertSame(1, $payload['summary']['tenantAssignmentsEnabled']);
        self::assertSame(1, $payload['summary']['billingActiveTotal']);
    }

    private function createPublishedApplicationWithAssignment(): Application
    {
        $suffix = bin2hex(random_bytes(4));
        $application = new Application(
            'API Demo ' . $suffix,
            'api-demo-' . $suffix,
            'applicating/api-demo-' . $suffix,
            'Applicating Labs',
            'API listing summary'
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
