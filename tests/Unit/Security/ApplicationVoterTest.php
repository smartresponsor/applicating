<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\Application;
use App\Entity\TenantApplication;
use App\Security\Voter\ApplicationVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class ApplicationVoterTest extends TestCase
{
    public function testManagerCanEditApplication(): void
    {
        $voter = new ApplicationVoter($this->createSecurityMock(['ROLE_APPLICATION_MANAGER']));
        $application = $this->createApplication();

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createTokenMock(), $application, [ApplicationVoter::EDIT])
        );
    }

    public function testManagerCannotPublishSuspendedApplication(): void
    {
        $voter = new ApplicationVoter($this->createSecurityMock(['ROLE_APPLICATION_MANAGER']));
        $application = $this->createApplication();
        $application->suspend();

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createTokenMock(), $application, [ApplicationVoter::PUBLISH])
        );
    }

    public function testManagerCanAssignOnlyPublishedApplication(): void
    {
        $voter = new ApplicationVoter($this->createSecurityMock(['ROLE_APPLICATION_MANAGER']));
        $draftApplication = $this->createApplication();
        $publishedApplication = $this->createApplication();
        $publishedApplication->publish();

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createTokenMock(), $draftApplication, [ApplicationVoter::ASSIGN])
        );
        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createTokenMock(), $publishedApplication, [ApplicationVoter::ASSIGN])
        );
    }

    public function testManagerCanToggleTenantApplication(): void
    {
        $voter = new ApplicationVoter($this->createSecurityMock(['ROLE_APPLICATION_MANAGER']));
        $tenantApplication = new TenantApplication(
            $this->createApplication(),
            'tenant-alpha',
            '1.0.0',
            true,
            true,
            ['scope' => 'tenant']
        );

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createTokenMock(), $tenantApplication, [ApplicationVoter::TOGGLE])
        );
    }

    public function testAdminBypassesPublicationStateRestrictions(): void
    {
        $voter = new ApplicationVoter($this->createSecurityMock(['ROLE_APPLICATION_ADMIN']));
        $application = $this->createApplication();
        $application->suspend();

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createTokenMock(), $application, [ApplicationVoter::PUBLISH])
        );
    }

    private function createApplication(): Application
    {
        return new Application(
            'Demo Application',
            'demo-application-' . bin2hex(random_bytes(4)),
            'applicating/demo-application-' . bin2hex(random_bytes(4)),
            'Applicating Labs',
            'Demo listing summary'
        );
    }

    private function createTokenMock(): TokenInterface
    {
        return $this->createMock(TokenInterface::class);
    }

    /** @param list<string> $grantedRoles */
    private function createSecurityMock(array $grantedRoles): Security
    {
        $security = $this->createMock(Security::class);
        $security->method('isGranted')->willReturnCallback(
            static fn (string $attribute): bool => in_array($attribute, $grantedRoles, true)
        );

        return $security;
    }
}
