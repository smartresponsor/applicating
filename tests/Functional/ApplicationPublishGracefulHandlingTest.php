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

final class ApplicationPublishGracefulHandlingTest extends WebTestCase
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

    public function testPublishWithoutManifestShowsDangerFlashAndKeepsDraftState(): void
    {
        [$application, $release] = $this->createApplicationWithReleaseOnly();

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $this->client->request('POST', sprintf('/admin/applications/%d/publish/%d', $application->getId(), $release->getId()));

        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Application cannot be published without a manifest.', (string) $this->client->getResponse()->getContent());

        self::assertSame('draft', $application->getPublicationState()->value);
        self::assertSame('draft', $release->getPublicationState()->value);
    }

    public function testPublishWithReviewRequiredManifestShowsDangerFlash(): void
    {
        [$application, $release] = $this->createApplicationWithManifest('review_required');

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $this->client->request('POST', sprintf('/admin/applications/%d/publish/%d', $application->getId(), $release->getId()));

        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Application cannot be published without an approved manifest.', (string) $this->client->getResponse()->getContent());

        self::assertSame('draft', $application->getPublicationState()->value);
        self::assertSame('draft', $release->getPublicationState()->value);
    }

    /** @return array{0: Application, 1: ApplicationRelease} */
    private function createApplicationWithReleaseOnly(): array
    {
        $application = new Application(
            'Publish Grace Application',
            'publish-grace-application',
            'applicating/publish-grace-application',
            'Applicating Labs',
            'Publish grace summary'
        );
        $release = new ApplicationRelease(
            $application,
            '1.0.0',
            'stable',
            hash('sha256', 'publish-grace'),
            'https://downloads.example.test/publish-grace/1.0.0.zip',
            'Publish grace release notes'
        );
        $application->addRelease($release);

        $this->entityManager->persist($application);
        $this->entityManager->persist($release);
        $this->entityManager->flush();

        return [$application, $release];
    }

    /** @return array{0: Application, 1: ApplicationRelease} */
    private function createApplicationWithManifest(string $governanceState): array
    {
        [$application, $release] = $this->createApplicationWithReleaseOnly();

        $manifest = new ApplicationManifest(
            $application,
            '1.0.0',
            'io.applicating.publish.grace.application',
            ['catalog'],
            ['tenant:read'],
            ['bootstrap'],
            'default',
            $governanceState,
            ['identifier' => 'io.applicating.publish.grace.application']
        );
        $application->addManifest($manifest);

        $this->entityManager->persist($manifest);
        $this->entityManager->flush();

        return [$application, $release];
    }
}
