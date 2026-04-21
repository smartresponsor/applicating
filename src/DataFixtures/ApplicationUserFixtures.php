<?php

declare(strict_types=1);

namespace App\Application\DataFixtures;

use App\Application\Entity\ApplicationUser;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ApplicationUserFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $this->createLocalUser(
            manager: $manager,
            userIdentifier: 'admin',
            displayName: 'Applicating Administrator',
            password: 'admin',
            roles: ['ROLE_APPLICATION_ADMIN', 'ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER'],
            email: 'admin@applicating.local'
        );

        $this->createLocalUser(
            manager: $manager,
            userIdentifier: 'manager',
            displayName: 'Applicating Manager',
            password: 'manager',
            roles: ['ROLE_APPLICATION_MANAGER', 'ROLE_APPLICATION_VIEWER'],
            email: 'manager@applicating.local'
        );

        $this->createLocalUser(
            manager: $manager,
            userIdentifier: 'viewer',
            displayName: 'Applicating Viewer',
            password: 'viewer',
            roles: ['ROLE_APPLICATION_VIEWER'],
            email: 'viewer@applicating.local'
        );

        $manager->flush();
    }

    /**
     * @param non-empty-string $userIdentifier
     * @param list<string>     $roles
     */
    private function createLocalUser(
        ObjectManager $manager,
        string $userIdentifier,
        string $displayName,
        string $password,
        array $roles,
        ?string $email = null,
    ): void {
        $user = new ApplicationUser($userIdentifier, $displayName);
        $user->changeRoles($roles);
        $user->changeEmail($email);
        $user->changeAuthSource('local');
        $user->changePassword($this->passwordHasher->hashPassword($user, $password));

        $manager->persist($user);
    }
}
