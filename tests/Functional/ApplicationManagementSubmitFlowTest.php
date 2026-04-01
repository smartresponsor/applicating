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

final class ApplicationManagementSubmitFlowTest extends WebTestCase
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

    public function testManagerCanAddReleaseFromShowPage(): void
    {
        $application = $this->createApplication('release-flow');

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $crawler = $this->client->request('GET', '/admin/applications/' . $application->getId());
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Add release')->form([
            'application_release[version]' => '1.2.3',
            'application_release[channel]' => 'stable',
            'application_release[checksum]' => hash('sha256', 'release-flow'),
            'application_release[downloadUrl]' => 'https://downloads.example.test/release-flow/1.2.3.zip',
            'application_release[releaseNotes]' => 'Release flow notes',
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects();
        $this->entityManager->clear();
        $reloaded = $this->applicationRepository->find($application->getId());
        self::assertInstanceOf(Application::class, $reloaded);
        self::assertCount(1, $reloaded->getReleases());
        self::assertSame('1.2.3', $reloaded->getReleases()->first()->getVersion());
    }

    public function testManagerCanAddManifestFromShowPage(): void
    {
        $application = $this->createApplication('manifest-flow');

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $crawler = $this->client->request('GET', '/admin/applications/' . $application->getId());
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Add manifest')->form([
            'application_manifest[manifestVersion]' => '1.0.0',
            'application_manifest[identifier]' => 'io.applicating.manifest.flow',
            'application_manifest[capabilities]' => "catalog\nreporting",
            'application_manifest[permissions]' => 'tenant:read',
            'application_manifest[runtimeHooks]' => 'bootstrap',
            'application_manifest[sandboxProfile]' => 'default',
            'application_manifest[governanceState]' => 'approved',
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects();
        $this->entityManager->clear();
        $reloaded = $this->applicationRepository->find($application->getId());
        self::assertInstanceOf(Application::class, $reloaded);
        self::assertCount(1, $reloaded->getManifests());
        self::assertSame('io.applicating.manifest.flow', $reloaded->getManifests()->first()->getIdentifier());
    }

    public function testManagerCanAssignPublishedApplicationFromShowPage(): void
    {
        $application = $this->createApplication('assign-flow');
        $application->publish();
        $this->entityManager->flush();

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $crawler = $this->client->request('GET', '/admin/applications/' . $application->getId());
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Assign application')->form([
            'tenant_application_assignment[tenantKey]' => 'tenant-assign-flow',
            'tenant_application_assignment[installedVersion]' => '1.0.0',
            'tenant_application_assignment[enabled]' => '1',
            'tenant_application_assignment[billingActive]' => '1',
            'tenant_application_assignment[accessPolicy]' => "{\n  \"scope\": \"tenant\"\n}",
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects();
        $this->entityManager->clear();
        $reloaded = $this->applicationRepository->find($application->getId());
        self::assertInstanceOf(Application::class, $reloaded);
        self::assertCount(1, $reloaded->getTenantApplications());
        self::assertSame('tenant-assign-flow', $reloaded->getTenantApplications()->first()->getTenantKey());
    }

    private function createApplication(string $slugBase): Application
    {
        $suffix = bin2hex(random_bytes(4));
        $application = new Application(
            ucfirst($slugBase) . ' Application',
            $slugBase . '-' . $suffix,
            'applicating/' . $slugBase . '-' . $suffix,
            'Applicating Labs',
            'Management flow summary'
        );

        $this->entityManager->persist($application);
        $this->entityManager->flush();

        return $application;
    }
}
