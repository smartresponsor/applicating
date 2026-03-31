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

final class ApplicationCrudInvalidFlowTest extends WebTestCase
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

    public function testInvalidCreateDoesNotPersistApplication(): void
    {
        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $crawler = $this->client->request('GET', '/admin/applications/new');

        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Create')->form([
            'application[name]' => '',
            'application[slug]' => 'Bad Slug',
            'application[packageName]' => '',
            'application[developerName]' => '',
            'application[listingSummary]' => '',
            'application[accessLevel]' => '',
            'application[sandboxProfile]' => '',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects();
        $this->entityManager->clear();
        self::assertNull($this->applicationRepository->findOneBy(['slug' => 'Bad Slug']));
    }

    public function testInvalidEditDoesNotOverwriteExistingApplication(): void
    {
        $application = new Application(
            'Original Invalid Edit Application',
            'original-invalid-edit-application',
            'applicating/original-invalid-edit-application',
            'Applicating Labs',
            'Original invalid edit summary'
        );
        $this->entityManager->persist($application);
        $this->entityManager->flush();

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $crawler = $this->client->request('GET', '/admin/applications/' . $application->getId() . '/edit');

        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save')->form([
            'application[name]' => '',
            'application[slug]' => 'Bad Slug',
            'application[packageName]' => '',
            'application[developerName]' => '',
            'application[listingSummary]' => '',
            'application[accessLevel]' => '',
            'application[sandboxProfile]' => '',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects();
        $this->entityManager->clear();
        $reloaded = $this->applicationRepository->find($application->getId());
        self::assertInstanceOf(Application::class, $reloaded);
        self::assertSame('Original Invalid Edit Application', $reloaded->getName());
        self::assertSame('original-invalid-edit-application', $reloaded->getSlug());
        self::assertSame('applicating/original-invalid-edit-application', $reloaded->getPackageName());
        self::assertSame('Applicating Labs', $reloaded->getDeveloperName());
        self::assertSame('Original invalid edit summary', $reloaded->getListingSummary());
    }
}
