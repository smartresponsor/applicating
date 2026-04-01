<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\DTO\Application\ApplicationManifestData;
use App\DTO\Application\ApplicationReleaseData;
use App\DTO\Application\ApplicationUpsertData;
use App\DTO\Application\TenantApplicationAssignmentData;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ApplicationDtoValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        /** @var ValidatorInterface $validator */
        $validator = static::getContainer()->get(ValidatorInterface::class);
        $this->validator = $validator;
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
    }

    public function testApplicationUpsertDataValidation(): void
    {
        $valid = new ApplicationUpsertData();
        $valid->name = 'Demo Application';
        $valid->slug = 'demo-application';
        $valid->packageName = 'applicating/demo-application';
        $valid->developerName = 'Applicating Labs';
        $valid->listingSummary = 'Demo listing summary';
        $valid->accessLevel = 'public';
        $valid->sandboxProfile = 'default';

        $this->assertNoViolations($this->validator->validate($valid));

        $invalid = new ApplicationUpsertData();
        $invalid->name = '';
        $invalid->slug = 'Bad Slug';
        $invalid->packageName = '';
        $invalid->developerName = '';
        $invalid->listingSummary = '';
        $invalid->accessLevel = '';
        $invalid->sandboxProfile = '';

        $violations = $this->validator->validate($invalid);
        $this->assertHasPropertyViolation($violations, 'name');
        $this->assertHasPropertyViolation($violations, 'slug');
        $this->assertHasPropertyViolation($violations, 'packageName');
        $this->assertHasPropertyViolation($violations, 'developerName');
        $this->assertHasPropertyViolation($violations, 'listingSummary');
        $this->assertHasPropertyViolation($violations, 'accessLevel');
        $this->assertHasPropertyViolation($violations, 'sandboxProfile');
    }

    public function testApplicationReleaseDataValidation(): void
    {
        $valid = new ApplicationReleaseData();
        $valid->version = '1.2.3';
        $valid->channel = 'stable';
        $valid->checksum = hash('sha256', 'release');
        $valid->downloadUrl = 'https://downloads.example.test/release/1.2.3.zip';
        $valid->releaseNotes = 'Release notes';

        $this->assertNoViolations($this->validator->validate($valid));

        $invalid = new ApplicationReleaseData();
        $invalid->version = '1.2';
        $invalid->channel = str_repeat('x', 40);
        $invalid->checksum = '';
        $invalid->downloadUrl = 'not-a-url';
        $invalid->releaseNotes = '';

        $violations = $this->validator->validate($invalid);
        $this->assertHasPropertyViolation($violations, 'version');
        $this->assertHasPropertyViolation($violations, 'channel');
        $this->assertHasPropertyViolation($violations, 'checksum');
        $this->assertHasPropertyViolation($violations, 'downloadUrl');
        $this->assertHasPropertyViolation($violations, 'releaseNotes');
    }

    public function testApplicationManifestDataValidation(): void
    {
        $valid = new ApplicationManifestData();
        $valid->manifestVersion = '1.0.0';
        $valid->identifier = 'io.applicating.demo.application';
        $valid->sandboxProfile = 'default';
        $valid->governanceState = 'approved';

        $this->assertNoViolations($this->validator->validate($valid));

        $invalid = new ApplicationManifestData();
        $invalid->manifestVersion = '';
        $invalid->identifier = 'bad identifier';
        $invalid->sandboxProfile = '';
        $invalid->governanceState = '';

        $violations = $this->validator->validate($invalid);
        $this->assertHasPropertyViolation($violations, 'manifestVersion');
        $this->assertHasPropertyViolation($violations, 'identifier');
        $this->assertHasPropertyViolation($violations, 'sandboxProfile');
        $this->assertHasPropertyViolation($violations, 'governanceState');
    }

    public function testTenantApplicationAssignmentDataValidation(): void
    {
        $valid = new TenantApplicationAssignmentData();
        $valid->tenantKey = 'tenant-alpha';
        $valid->installedVersion = '1.0.0';

        $this->assertNoViolations($this->validator->validate($valid));

        $invalid = new TenantApplicationAssignmentData();
        $invalid->tenantKey = '';
        $invalid->installedVersion = '1.0';

        $violations = $this->validator->validate($invalid);
        $this->assertHasPropertyViolation($violations, 'tenantKey');
        $this->assertHasPropertyViolation($violations, 'installedVersion');
    }

    private function assertNoViolations(ConstraintViolationListInterface $violations): void
    {
        self::assertCount(0, $violations, (string) $violations);
    }

    private function assertHasPropertyViolation(ConstraintViolationListInterface $violations, string $propertyPath): void
    {
        foreach ($violations as $violation) {
            if ($violation->getPropertyPath() === $propertyPath) {
                self::assertTrue(true);

                return;
            }
        }

        self::fail(sprintf('Expected violation for property "%s". Actual violations: %s', $propertyPath, (string) $violations));
    }
}
