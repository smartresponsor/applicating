<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Application;
use App\Entity\ApplicationManifest;
use App\Entity\ApplicationRelease;
use App\Entity\TenantApplication;
use App\Tests\Support\DoctrineSchemaResetter;
use App\Tests\Support\Security\TestBrowserUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ApplicationShowPageBehaviorTest extends WebTestCase
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

    public function testViewerSeesReadOnlyShowPage(): void
    {
        $application = $this->createApplicationAggregate(true);

        $this->client->loginUser(new TestBrowserUser('viewer-test', ['ROLE_APPLICATION_VIEWER']));
        $this->client->request('GET', '/admin/applications/' . $application->getId());

        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();

        self::assertStringContainsString('Releases', $content);
        self::assertStringContainsString('Manifests', $content);
        self::assertStringContainsString('Tenant assignments', $content);
        self::assertStringNotContainsString('Edit', $content);
        self::assertStringNotContainsString('Add release', $content);
        self::assertStringNotContainsString('Add manifest', $content);
        self::assertStringNotContainsString('Assign application', $content);
    }

    public function testManagerSeesManagementActionsOnPublishedApplication(): void
    {
        $application = $this->createApplicationAggregate(true);

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $this->client->request('GET', '/admin/applications/' . $application->getId());

        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();

        self::assertStringContainsString('Edit', $content);
        self::assertStringContainsString('Add release', $content);
        self::assertStringContainsString('Add manifest', $content);
        self::assertStringContainsString('Assign application', $content);
        self::assertStringContainsString('Suspend', $content);
    }

    public function testManagerDoesNotSeeAssignActionOnDraftApplication(): void
    {
        $application = $this->createApplicationAggregate(false);

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $this->client->request('GET', '/admin/applications/' . $application->getId());

        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();

        self::assertStringContainsString('Edit', $content);
        self::assertStringContainsString('Add release', $content);
        self::assertStringContainsString('Add manifest', $content);
        self::assertStringNotContainsString('Assign application', $content);
        self::assertStringContainsString('Publish', $content);
    }

    private function createApplicationAggregate(bool $published): Application
    {
        $suffix = bin2hex(random_bytes(4));
        $application = new Application(
            'UI Demo ' . $suffix,
            'ui-demo-' . $suffix,
            'applicating/ui-demo-' . $suffix,
            'Applicating Labs',
            'UI listing summary'
        );

        $release = new ApplicationRelease(
            $application,
            '1.0.0',
            'stable',
            hash('sha256', 'ui-release-' . $suffix),
            'https://downloads.example.test/ui-demo/' . $suffix . '/1.0.0.zip',
            'UI release notes'
        );
        $application->addRelease($release);

        $manifest = new ApplicationManifest(
            $application,
            '1.0.0',
            'io.applicating.ui.' . $suffix,
            ['catalog'],
            ['tenant:read'],
            ['bootstrap'],
            'default',
            'approved',
            ['identifier' => 'io.applicating.ui.' . $suffix]
        );
        $application->addManifest($manifest);

        $tenantApplication = new TenantApplication(
            $application,
            'tenant-' . $suffix,
            '1.0.0',
            true,
            true,
            ['scope' => 'tenant']
        );
        $application->addTenantApplication($tenantApplication);

        if ($published) {
            $application->publish();
            $release->publish();
        }

        $this->entityManager->persist($application);
        $this->entityManager->persist($release);
        $this->entityManager->persist($manifest);
        $this->entityManager->persist($tenantApplication);
        $this->entityManager->flush();

        return $application;
    }
}
