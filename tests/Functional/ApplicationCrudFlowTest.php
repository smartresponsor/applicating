<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Application;
use App\Repository\ApplicationRepository;
use App\Tests\Support\DoctrineSchemaResetter;
use App\Tests\Support\Security\TestBrowserUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ApplicationCrudFlowTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private ApplicationRepository $applicationRepository;

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

        /** @var ApplicationRepository $applicationRepository */
        $applicationRepository = static::getContainer()->get(ApplicationRepository::class);
        $this->applicationRepository = $applicationRepository;
    }

    public function testManagerCanCreateApplicationFromAdminForm(): void
    {
        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $crawler = $this->client->request('GET', '/admin/applications/new');

        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Create')->form([
            'application[name]' => 'CRUD Demo Application',
            'application[slug]' => 'crud-demo-application',
            'application[packageName]' => 'applicating/crud-demo-application',
            'application[developerName]' => 'Applicating Labs',
            'application[listingSummary]' => 'CRUD listing summary',
            'application[accessLevel]' => 'public',
            'application[billingCode]' => 'APP-CRUD',
            'application[sandboxProfile]' => 'default',
            'application[enabledByDefault]' => '1',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects();
        $createdApplication = $this->applicationRepository->findOneBy(['slug' => 'crud-demo-application']);
        self::assertInstanceOf(Application::class, $createdApplication);
        self::assertSame('CRUD Demo Application', $createdApplication->getName());
        self::assertSame('APP-CRUD', $createdApplication->getBillingCode());
        self::assertTrue($createdApplication->isEnabledByDefault());
    }

    public function testManagerCanEditApplicationFromAdminForm(): void
    {
        $application = new Application(
            'Original Application',
            'original-application',
            'applicating/original-application',
            'Applicating Labs',
            'Original listing summary'
        );
        $this->entityManager->persist($application);
        $this->entityManager->flush();

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $crawler = $this->client->request('GET', '/admin/applications/' . $application->getId() . '/edit');

        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save')->form([
            'application[name]' => 'Updated Application',
            'application[slug]' => 'updated-application',
            'application[packageName]' => 'applicating/updated-application',
            'application[developerName]' => 'Updated Labs',
            'application[listingSummary]' => 'Updated listing summary',
            'application[accessLevel]' => 'private',
            'application[billingCode]' => 'APP-UPDATED',
            'application[sandboxProfile]' => 'restricted',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects();

        $this->entityManager->clear();
        $updatedApplication = $this->applicationRepository->find($application->getId());
        self::assertInstanceOf(Application::class, $updatedApplication);
        self::assertSame('Updated Application', $updatedApplication->getName());
        self::assertSame('updated-application', $updatedApplication->getSlug());
        self::assertSame('applicating/updated-application', $updatedApplication->getPackageName());
        self::assertSame('Updated Labs', $updatedApplication->getDeveloperName());
        self::assertSame('Updated listing summary', $updatedApplication->getListingSummary());
        self::assertSame('APP-UPDATED', $updatedApplication->getBillingCode());
        self::assertSame('restricted', $updatedApplication->getSandboxProfile());
        self::assertFalse($updatedApplication->isEnabledByDefault());
    }
}
