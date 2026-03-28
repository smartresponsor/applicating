<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\DTO\Application\ApplicationUpsertData;
use App\ServiceInterface\ApplicationLifecycleServiceInterface;
use App\Tests\Support\DoctrineSchemaResetter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ApplicationManagementControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $container = static::getContainer();
        /** @var ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();
        DoctrineSchemaResetter::reset($entityManager);

        $data = new ApplicationUpsertData();
        $data->name = 'Managed Application';
        $data->slug = 'managed-application';
        $data->packageName = 'applicating/managed-application';
        $data->developerName = 'Applicating Labs';
        $data->listingSummary = 'Managed application listing';
        /** @var ApplicationLifecycleServiceInterface $service */
        $service = $container->get(ApplicationLifecycleServiceInterface::class);
        $service->createApplication($data);

        $this->client->request('GET', '/login');
        $this->client->submitForm('Sign in', [
            '_username' => 'admin',
            '_password' => 'admin',
        ]);
    }

    public function testAdminIndexAndApiUseApplicationVocabulary(): void
    {
        $this->client->request('GET', '/admin/applications');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Application Lifecycle Management');

        $this->client->request('GET', '/api/admin/applications');
        self::assertResponseIsSuccessful();
        /** @var array{applications: list<array{slug: string}>} $payload */
        $payload = json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('applications', $payload);
        self::assertSame('managed-application', $payload['applications'][0]['slug']);
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }
}
