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

final class ApplicationManagementInvalidSubmitFlowTest extends WebTestCase
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

    public function testInvalidReleaseSubmissionDoesNotPersistRelease(): void
    {
        $application = $this->createApplication('invalid-release-flow');

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $crawler = $this->client->request('GET', '/admin/applications/' . $application->getId());
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Add release')->form([
            'application_release[version]' => 'bad-version',
            'application_release[channel]' => 'stable',
            'application_release[checksum]' => 'checksum',
            'application_release[downloadUrl]' => 'not-a-url',
            'application_release[releaseNotes]' => '',
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects();
        $this->entityManager->clear();
        $reloaded = $this->applicationRepository->find($application->getId());
        self::assertInstanceOf(Application::class, $reloaded);
        self::assertCount(0, $reloaded->getReleases());
    }

    public function testInvalidManifestSubmissionDoesNotPersistManifest(): void
    {
        $application = $this->createApplication('invalid-manifest-flow');

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $crawler = $this->client->request('GET', '/admin/applications/' . $application->getId());
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Add manifest')->form([
            'application_manifest[manifestVersion]' => '',
            'application_manifest[identifier]' => 'bad identifier',
            'application_manifest[capabilities]' => '',
            'application_manifest[permissions]' => '',
            'application_manifest[runtimeHooks]' => '',
            'application_manifest[sandboxProfile]' => '',
            'application_manifest[governanceState]' => '',
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects();
        $this->entityManager->clear();
        $reloaded = $this->applicationRepository->find($application->getId());
        self::assertInstanceOf(Application::class, $reloaded);
        self::assertCount(0, $reloaded->getManifests());
    }

    public function testInvalidTenantAssignmentSubmissionDoesNotPersistAssignment(): void
    {
        $application = $this->createApplication('invalid-assign-flow');
        $application->publish();
        $this->entityManager->flush();

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $crawler = $this->client->request('GET', '/admin/applications/' . $application->getId());
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Assign application')->form([
            'tenant_application_assignment[tenantKey]' => '',
            'tenant_application_assignment[installedVersion]' => 'bad-version',
            'tenant_application_assignment[enabled]' => '1',
            'tenant_application_assignment[billingActive]' => '1',
            'tenant_application_assignment[accessPolicy]' => '{bad-json}',
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects();
        $this->entityManager->clear();
        $reloaded = $this->applicationRepository->find($application->getId());
        self::assertInstanceOf(Application::class, $reloaded);
        self::assertCount(0, $reloaded->getTenantApplications());
    }

    private function createApplication(string $slugBase): Application
    {
        $suffix = bin2hex(random_bytes(4));
        $application = new Application(
            ucfirst($slugBase) . ' Application',
            $slugBase . '-' . $suffix,
            'applicating/' . $slugBase . '-' . $suffix,
            'Applicating Labs',
            'Invalid flow summary'
        );

        $this->entityManager->persist($application);
        $this->entityManager->flush();

        return $application;
    }
}
