<?php

declare(strict_types=1);

namespace App\Applicating\Command;

use App\Applicating\Entity\ApplicationUser;
use App\Applicating\Repository\ApplicationUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'applicating:user:create', description: 'Create or update a persistent Applicating local user')]
final class ApplicatingUserCreateCommand extends Command
{
    public function __construct(
        private readonly ApplicationUserRepository $applicationUserRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('identifier', InputArgument::REQUIRED, 'Login identifier for the user')
            ->addArgument('password', InputArgument::REQUIRED, 'Plaintext password to hash and store')
            ->addOption('display-nameEntity', null, InputOption::VALUE_REQUIRED, 'Display nameEntity for the user')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Email for the user')
            ->addOption('role', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Role(s) to assign', ['ROLE_APPLICATION_VIEWER']);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $arguments = $this->validatedArguments($input, $output);
        if (null === $arguments) {
            return Command::INVALID;
        }

        [$identifier, $password] = $arguments;
        $displayName = $this->optionalString($input->getOption('display-nameEntity')) ?? ucfirst($identifier);
        $email = $this->optionalString($input->getOption('email'));
        $normalizedRoles = $this->normalizedRoles($input->getOption('role'));

        $user = $this->applicationUserRepository->findOneByIdentifier($identifier);
        $created = null === $user;

        if (null === $user) {
            $user = new ApplicationUser($identifier, $displayName);
        }

        $user->renameDisplayName($displayName);
        $user->changeEmail($email);
        $user->changeRoles($normalizedRoles);
        $user->changeAuthSource('local');
        $user->activate();
        $user->changePassword($this->passwordHasher->hashPassword($user, $password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln(sprintf(
            '<info>%s persistent user "%s" with roles [%s].</info>',
            $created ? 'Created' : 'Updated',
            $identifier,
            implode(', ', $user->getRoles())
        ));

        return Command::SUCCESS;
    }

    /** @return array{non-empty-string, non-empty-string}|null */
    private function validatedArguments(InputInterface $input, OutputInterface $output): ?array
    {
        $identifier = $input->getArgument('identifier');
        if (!is_string($identifier) || '' === trim($identifier)) {
            $output->writeln('<error>The identifier argument must be a non-empty string.</error>');

            return null;
        }

        $password = $input->getArgument('password');
        if (!is_string($password) || '' === $password) {
            $output->writeln('<error>The password argument must be a non-empty string.</error>');

            return null;
        }

        return [$identifier, $password];
    }

    private function optionalString(mixed $value): ?string
    {
        return is_string($value) && '' !== trim($value) ? $value : null;
    }

    /** @return list<string> */
    private function normalizedRoles(mixed $value): array
    {
        $roles = is_array($value) && [] !== $value ? $value : ['ROLE_APPLICATION_VIEWER'];

        return array_values(array_filter(
            array_map(static fn (mixed $role): string => is_string($role) ? $role : '', $roles),
            static fn (string $role): bool => '' !== trim($role),
        ));
    }
}
