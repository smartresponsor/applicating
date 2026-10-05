<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\Service;

use App\Applicating\DTO\ApplicationManifestDTO;
use App\Applicating\DTO\ApplicationReleaseDTO;
use App\Applicating\DTO\ApplicationTenantAssignmentDTO;
use App\Applicating\DTO\ApplicationTenantDiagnosticsChecksDTO;
use App\Applicating\DTO\ApplicationTenantDiagnosticsDTO;
use App\Applicating\DTO\ApplicationUpsertDTO;
use App\Applicating\Entity\Application\ApplicationEntity;
use App\Applicating\Entity\ApplicationManifestEntity;
use App\Applicating\Entity\ApplicationReleaseEntity;
use App\Applicating\Enum\ApplicationAccessLevel;
use App\Applicating\Enum\ApplicationPublicationState;
use App\Applicating\RepositoryInterface\ApplicationManifestRepositoryInterface;
use App\Applicating\RepositoryInterface\ApplicationReleaseRepositoryInterface;
use App\Applicating\RepositoryInterface\ApplicationRepositoryInterface;
use App\Applicating\RepositoryInterface\ApplicationTenantAssignmentRepositoryInterface;
use App\Applicating\Service\ApplicationLifecycleService;
use App\Applicating\ServiceInterface\ApplicationDiagnosticsServiceInterface;
use App\Applicating\ServiceInterface\ApplicationManifestServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ApplicationLifecycleServiceTest extends TestCase
{
    private ApplicationRepositoryInterface&MockObject $applicationRepository;
    private ApplicationManifestServiceInterface&MockObject $manifestService;
    private ApplicationDiagnosticsServiceInterface&MockObject $diagnosticsService;
    private ApplicationReleaseRepositoryInterface&MockObject $releaseRepository;
    private ApplicationManifestRepositoryInterface&MockObject $manifestRepository;
    private ApplicationTenantAssignmentRepositoryInterface&MockObject $tenantRepository;
    private ApplicationLifecycleService $service;

    protected function setUp(): void
    {
        $this->applicationRepository = $this->createMock(ApplicationRepositoryInterface::class);
        $this->manifestService = $this->createMock(ApplicationManifestServiceInterface::class);
        $this->diagnosticsService = $this->createMock(ApplicationDiagnosticsServiceInterface::class);
        $this->releaseRepository = $this->createMock(ApplicationReleaseRepositoryInterface::class);
        $this->manifestRepository = $this->createMock(ApplicationManifestRepositoryInterface::class);
        $this->tenantRepository = $this->createMock(ApplicationTenantAssignmentRepositoryInterface::class);
        $this->service = new ApplicationLifecycleService(
            $this->applicationRepository,
            $this->manifestService,
            $this->diagnosticsService,
            $this->releaseRepository,
            $this->manifestRepository,
            $this->tenantRepository,
        );
    }

    public function testApplicationLifecycleMutationsPersistState(): void
    {
        $this->applicationRepository->expects(self::exactly(3))->method('save');
        $application = $this->service->createApplication($this->upsertData());

        self::assertSame(ApplicationAccessLevel::TenantRestricted, $application->getAccessLevel());
        self::assertSame('BILL-42', $application->getBillingCode());
        self::assertTrue($application->isEnabledByDefault());

        $update = $this->upsertData();
        $update->nameEntity = 'Updated Application';
        $update->slug = 'updated-application';
        $update->accessLevel = ApplicationAccessLevel::Public->value;
        $update->billingCode = '';
        $update->enabledByDefault = false;
        self::assertSame($application, $this->service->updateApplication($application, $update));
        self::assertSame('Updated Application', $application->getName());
        self::assertNull($application->getBillingCode());

        $this->service->suspendApplication($application);
        self::assertSame(ApplicationPublicationState::Suspended, $application->getPublicationState());
    }

    public function testReleaseAndPublishLifecycleCoversSuccessAndRejections(): void
    {
        $application = $this->application();
        $data = $this->releaseData();
        $existing = new ApplicationReleaseEntity($application, '1.2.3', 'stable', 'abc', 'https://example.test/old.zip', 'Old.');
        $this->releaseRepository
            ->expects(self::exactly(2))
            ->method('findOneForApplicationAndVersion')
            ->willReturnOnConsecutiveCalls($existing, null);

        $this->assertLogicException(
            fn () => $this->service->createRelease($application, $data),
            'Release version 1.2.3 already exists for application demo-application.',
        );

        $this->releaseRepository->expects(self::exactly(2))->method('save');
        $release = $this->service->createRelease($application, $data);
        self::assertCount(1, $application->getReleases());

        $this->assertLogicException(
            fn () => $this->service->publishApplication($application, $release),
            'Application cannot be published without a manifest.',
        );
        $application->addManifest(new ApplicationManifestEntity($application, '1.0', 'demo.pending', [], [], [], 'default', 'pending', []));
        $this->assertLogicException(
            fn () => $this->service->publishApplication($application, $release),
            'Application cannot be published without an approved manifest.',
        );
        $application->addManifest(new ApplicationManifestEntity($application, '1.0', 'demo.approved', [], [], [], 'default', 'approved', []));
        $this->service->publishApplication($application, $release);
        self::assertSame(ApplicationPublicationState::Published, $application->getPublicationState());
        self::assertSame(ApplicationPublicationState::Published, $release->getPublicationState());

        $this->assertLogicException(
            fn () => $this->service->publishApplication($application, $release),
            'Application release is already published.',
        );
    }

    public function testManifestAndTenantAssignmentLifecycle(): void
    {
        $application = $this->application();
        $manifestData = new ApplicationManifestDTO();
        $manifestData->identifier = 'demo.manifest';
        $payload = [
            'manifestVersion' => '1.0.0',
            'identifier' => 'demo.manifest',
            'capabilities' => ['catalog.read'],
            'permissions' => ['ROLE_APPLICATION_USER'],
            'runtimeHooks' => ['boot'],
            'sandboxProfile' => 'isolated',
            'governanceState' => 'approved',
        ];
        $this->manifestService->method('normalizeManifestPayload')->willReturn($payload);
        $this->manifestRepository->method('findOneForApplicationAndIdentifier')->willReturn(null);
        $this->manifestRepository->expects(self::once())->method('save');

        $manifest = $this->service->createManifest($application, $manifestData);
        self::assertSame('demo.manifest', $manifest->getIdentifier());
        self::assertSame(['boot'], $manifest->getRuntimeHooks());

        $assignmentData = new ApplicationTenantAssignmentDTO();
        $assignmentData->tenantKey = 'tenant-1';
        $assignmentData->installedVersion = '1.2.3';
        $assignmentData->accessPolicy = '{"scope":"tenant"}';
        $existing = null;
        $this->tenantRepository
            ->expects(self::exactly(2))
            ->method('findOneForTenantAndApplicationEntity')
            ->willReturnCallback(static function () use (&$existing) {
                return $existing;
            });
        $this->diagnosticsService->expects(self::exactly(4))->method('buildTenantDiagnostics')->willReturn($this->diagnostics());
        $this->tenantRepository->expects(self::exactly(4))->method('save');

        $assignment = $this->service->assignTenant($application, $assignmentData);
        $existing = $assignment;
        self::assertSame(['scope' => 'tenant'], $assignment->getAccessPolicy());

        $assignmentData->installedVersion = '2.0.0';
        $assignmentData->enabled = false;
        $assignmentData->billingActive = false;
        $assignmentData->accessPolicy = '{"scope":"restricted"}';
        self::assertSame($assignment, $this->service->assignTenant($application, $assignmentData));
        self::assertSame('2.0.0', $assignment->getInstalledVersion());
        self::assertFalse($assignment->isEnabled());

        $this->service->toggleTenantApplication($assignment, true);
        self::assertTrue($assignment->isEnabled());
        $this->service->toggleTenantApplication($assignment, false);
        self::assertFalse($assignment->isEnabled());
        self::assertNotSame([], $assignment->getDiagnostics());
    }

    private function upsertData(): ApplicationUpsertDTO
    {
        $data = new ApplicationUpsertDTO();
        $data->nameEntity = 'Demo Application';
        $data->slug = 'demo-application';
        $data->packageName = 'demo/application';
        $data->developerName = 'Demo Developer';
        $data->listingSummary = 'Demo summary.';
        $data->accessLevel = ApplicationAccessLevel::TenantRestricted->value;
        $data->billingCode = 'BILL-42';
        $data->sandboxProfile = 'isolated';
        $data->enabledByDefault = true;

        return $data;
    }

    private function releaseData(): ApplicationReleaseDTO
    {
        $data = new ApplicationReleaseDTO();
        $data->version = '1.2.3';
        $data->channel = 'stable';
        $data->checksum = 'abc123';
        $data->downloadUrl = 'https://example.test/app.zip';
        $data->releaseNotes = 'Release notes.';

        return $data;
    }

    private function application(): ApplicationEntity
    {
        return new ApplicationEntity('Demo Application', 'demo-application', 'demo/application', 'Demo Developer', 'Demo summary.');
    }

    private function diagnostics(): ApplicationTenantDiagnosticsDTO
    {
        return new ApplicationTenantDiagnosticsDTO(
            'tenant-1',
            'demo-application',
            '1.2.3',
            true,
            true,
            'installed',
            new ApplicationTenantDiagnosticsChecksDTO(true, true, 'isolated', ['scope']),
            '2026-10-05T19:00:00+00:00',
        );
    }

    /** @param callable(): mixed $operation */
    private function assertLogicException(callable $operation, string $message): void
    {
        try {
            $operation();
            self::fail('Expected LogicException.');
        } catch (\LogicException $exception) {
            self::assertSame($message, $exception->getMessage());
        }
    }
}
