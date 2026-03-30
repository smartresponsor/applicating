<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Application;
use App\Entity\ApplicationManifest;
use App\Entity\ApplicationRelease;
use App\Tests\Support\DoctrineSchemaResetter;
use App\Tests\Support\Security\TestBrowserUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ApplicationFlashMessageFlowTest extends WebTestCase
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

    public function testCreateApplicationShowsSuccessFlash(): void
    {
        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $crawler = $this->client->request('GET', '/admin/applications/new');

        $form = $crawler->selectButton('Create')->form([
            'application[name]' => 'Flash Demo Application',
            'application[slug]' => 'flash-demo-application',
            'application[packageName]' => 'applicating/flash-demo-application',
            'application[developerName]' => 'Applicating Labs',
            'application[listingSummary]' => 'Flash listing summary',
            'application[accessLevel]' => 'public',
            'application[sandboxProfile]' => 'default',
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Application "Flash Demo Application" created.', (string) $this->client->getResponse()->getContent());
    }

    public function testSuspendApplicationShowsWarningFlash(): void
    {
        [$application] = $this->createPublishedApplicationAggregate();

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $this->client->request('POST', '/admin/applications/' . $application->getId() . '/suspend');

        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Application "' . $application->getName() . '" suspended.', (string) $this->client->getResponse()->getContent());
    }

    public function testInvalidReleaseSubmissionShowsDangerFlash(): void
    {
        $application = $this->createApplication();

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $crawler = $this->client->request('GET', '/admin/applications/' . $application->getId());

        $form = $crawler->selectButton('Add release')->form([
            'application_release[version]' => 'bad-version',
            'application_release[channel]' => 'stable',
            'application_release[checksum]' => '',
            'application_release[downloadUrl]' => 'not-a-url',
            'application_release[releaseNotes]' => '',
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Release form contains errors.', (string) $this->client->getResponse()->getContent());
    }

    private function createApplication(): Application
    {
        $suffix = bin2hex(random_bytes(4));
        $application = new Application(
            'Flash Aggregate ' . $suffix,
            'flash-aggregate-' . $suffix,
            'applicating/flash-aggregate-' . $suffix,
            'Applicating Labs',
            'Flash aggregate summary'
        );

        $this->entityManager->persist($application);
        $this->entityManager->flush();

        return $application;
    }

    /** @return array{0: Application, 1: ApplicationRelease} */
    private function createPublishedApplicationAggregate(): array
    {
        $application = $this->createApplication();
        $release = new ApplicationRelease(
            $application,
            '1.0.0',
            'stable',
            hash('sha256', 'flash-release'),
            'https://downloads.example.test/flash-release/1.0.0.zip',
            'Flash release notes'
        );
        $application->addRelease($release);

        $manifest = new ApplicationManifest(
            $application,
            '1.0.0',
            'io.applicating.flash.aggregate',
            ['catalog'],
            ['tenant:read'],
            ['bootstrap'],
            'default',
            'approved',
            ['identifier' => 'io.applicating.flash.aggregate']
        );
        $application->addManifest($manifest);

        $application->publish();
        $release->publish();

        $this->entityManager->persist($release);
        $this->entityManager->persist($manifest);
        $this->entityManager->flush();

        return [$application, $release];
    }
}
