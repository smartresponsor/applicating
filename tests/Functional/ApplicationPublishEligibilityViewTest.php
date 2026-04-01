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

final class ApplicationPublishEligibilityViewTest extends WebTestCase
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

    public function testShowPageExplainsManifestRequirementWhenPublishIsIneligible(): void
    {
        [$application, $release] = $this->createApplicationAggregate(null);

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $this->client->request('GET', sprintf('/admin/applications/%d', $application->getId()));

        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('Publish requires an attached manifest.', $content);
        self::assertStringNotContainsString(
            sprintf('/admin/applications/%d/publish/%d', $application->getId(), $release->getId()),
            $content
        );
    }

    public function testShowPageExplainsApprovedManifestRequirementWhenPublishIsIneligible(): void
    {
        [$application, $release] = $this->createApplicationAggregate('review_required');

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $this->client->request('GET', sprintf('/admin/applications/%d', $application->getId()));

        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('Publish requires an approved manifest.', $content);
        self::assertStringNotContainsString(
            sprintf('/admin/applications/%d/publish/%d', $application->getId(), $release->getId()),
            $content
        );
    }

    public function testShowPageKeepsPublishActionVisibleForEligibleRelease(): void
    {
        [$application, $release] = $this->createApplicationAggregate('approved');

        $this->client->loginUser(new TestBrowserUser('manager-test', ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER']));
        $this->client->request('GET', sprintf('/admin/applications/%d', $application->getId()));

        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString(
            sprintf('/admin/applications/%d/publish/%d', $application->getId(), $release->getId()),
            $content
        );
        self::assertStringNotContainsString('Publish requires an attached manifest.', $content);
        self::assertStringNotContainsString('Publish requires an approved manifest.', $content);
    }

    /** @return array{0: Application, 1: ApplicationRelease} */
    private function createApplicationAggregate(?string $governanceState): array
    {
        $suffix = bin2hex(random_bytes(4));
        $application = new Application(
            'Publish Eligibility Application ' . $suffix,
            'publish-eligibility-' . $suffix,
            'applicating/publish-eligibility-' . $suffix,
            'Applicating Labs',
            'Publish eligibility summary'
        );
        $release = new ApplicationRelease(
            $application,
            '1.0.0',
            'stable',
            hash('sha256', 'publish-eligibility-' . $suffix),
            'https://downloads.example.test/publish-eligibility/' . $suffix . '/1.0.0.zip',
            'Publish eligibility release notes'
        );
        $application->addRelease($release);

        $this->entityManager->persist($application);
        $this->entityManager->persist($release);

        if (null !== $governanceState) {
            $manifest = new ApplicationManifest(
                $application,
                '1.0.0',
                'io.applicating.publish.eligibility.' . $suffix,
                ['catalog'],
                ['tenant:read'],
                ['bootstrap'],
                'default',
                $governanceState,
                ['identifier' => 'io.applicating.publish.eligibility.' . $suffix]
            );
            $application->addManifest($manifest);
            $this->entityManager->persist($manifest);
        }

        $this->entityManager->flush();

        return [$application, $release];
    }
}
