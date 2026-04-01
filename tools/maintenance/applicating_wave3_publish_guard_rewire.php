<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$changed = [];

$lifecyclePath = $root . '/src/Service/ApplicationLifecycleService.php';
if (is_file($lifecyclePath)) {
    $content = file_get_contents($lifecyclePath);
    if (is_string($content) && str_contains($content, 'public function publishApplication(Application $application, ApplicationRelease $release): void')) {
        $original = <<<'PHP'
    public function publishApplication(Application $application, ApplicationRelease $release): void
    {
        $application->markForModeration();
        $application->publish();
        $release->publish();
        $this->entityManager->flush();
    }
PHP;

        $replacement = <<<'PHP'
    public function publishApplication(Application $application, ApplicationRelease $release): void
    {
        $this->assertApplicationPublishable($application, $release);
        $application->markForModeration();
        $application->publish();
        $release->publish();
        $this->entityManager->flush();
    }

    private function assertApplicationPublishable(Application $application, ApplicationRelease $release): void
    {
        if ($release->getApplication() !== $application) {
            throw new \LogicException('Cannot publish a release that does not belong to the given application.');
        }

        if ($release->getPublicationState() === \App\Enum\ApplicationPublicationState::Published) {
            throw new \LogicException('Cannot publish a release that is already published.');
        }

        if ($application->getManifests()->count() === 0) {
            throw new \LogicException('Cannot publish an application without at least one manifest.');
        }

        foreach ($application->getManifests() as $manifest) {
            if ($manifest->getGovernanceState() === 'approved') {
                return;
            }
        }

        throw new \LogicException('Cannot publish an application without an approved manifest governance state.');
    }
PHP;

        $updated = str_replace($original, $replacement, $content, $count);
        if ($count > 0) {
            file_put_contents($lifecyclePath, $updated);
            $changed[] = 'src/Service/ApplicationLifecycleService.php';
        }
    }
}

$testPath = $root . '/tests/Integration/ApplicationLifecycleServiceTest.php';
if (is_file($testPath)) {
    $content = file_get_contents($testPath);
    if (is_string($content) && !str_contains($content, 'testCannotPublishWithoutManifest')) {
        $insertion = <<<'PHP'

    public function testCannotPublishWithoutManifest(): void
    {
        $applicationData = new ApplicationUpsertData();
        $applicationData->name = 'Guarded Application';
        $applicationData->slug = 'guarded-application';
        $applicationData->packageName = 'applicating/guarded-application';
        $applicationData->developerName = 'Applicating Labs';
        $applicationData->listingSummary = 'Guarded listing';

        $application = $this->applicationLifecycleService->createApplication($applicationData);

        $releaseData = new ApplicationReleaseData();
        $releaseData->version = '1.0.0';
        $releaseData->checksum = hash('sha256', 'guarded');
        $releaseData->downloadUrl = 'https://downloads.example.test/guarded-application/1.0.0.zip';
        $releaseData->releaseNotes = 'Guarded release';
        $release = $this->applicationLifecycleService->createRelease($application, $releaseData);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cannot publish an application without at least one manifest.');
        $this->applicationLifecycleService->publishApplication($application, $release);
    }

    public function testCannotPublishWithoutApprovedManifest(): void
    {
        $applicationData = new ApplicationUpsertData();
        $applicationData->name = 'Moderation Application';
        $applicationData->slug = 'moderation-application';
        $applicationData->packageName = 'applicating/moderation-application';
        $applicationData->developerName = 'Applicating Labs';
        $applicationData->listingSummary = 'Moderation listing';

        $application = $this->applicationLifecycleService->createApplication($applicationData);

        $releaseData = new ApplicationReleaseData();
        $releaseData->version = '1.0.0';
        $releaseData->checksum = hash('sha256', 'moderation');
        $releaseData->downloadUrl = 'https://downloads.example.test/moderation-application/1.0.0.zip';
        $releaseData->releaseNotes = 'Moderation release';
        $release = $this->applicationLifecycleService->createRelease($application, $releaseData);

        $manifestData = new ApplicationManifestData();
        $manifestData->identifier = 'io.applicating.moderation.application';
        $manifestData->governanceState = 'moderation';
        $this->applicationLifecycleService->createManifest($application, $manifestData);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cannot publish an application without an approved manifest governance state.');
        $this->applicationLifecycleService->publishApplication($application, $release);
    }
PHP;

        $updated = str_replace("\n}\n", $insertion . "\n}\n", $content, $count);
        if ($count > 0) {
            file_put_contents($testPath, $updated);
            $changed[] = 'tests/Integration/ApplicationLifecycleServiceTest.php';
        }
    }
}

fwrite(STDOUT, "Applicating wave 3 publish-guard rewrite touched:\n");
foreach ($changed as $file) {
    fwrite(STDOUT, " - {$file}\n");
}
