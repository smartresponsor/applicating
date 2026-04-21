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
            ->addOption('display-name', null, InputOption::VALUE_REQUIRED, 'Display name for the user')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Email for the user')
            ->addOption('role', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Role(s) to assign', ['ROLE_APPLICATION_VIEWER']);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $identifierArgument = $input->getArgument('identifier');
        $passwordArgument = $input->getArgument('password');
        $displayNameOption = $input->getOption('display-name');
        $emailOption = $input->getOption('email');
        $rolesOption = $input->getOption('role');

        if (!is_string($identifierArgument) || '' === trim($identifierArgument)) {
            $output->writeln('<error>The identifier argument must be a non-empty string.</error>');

            return Command::INVALID;
        }

        if (!is_string($passwordArgument) || '' === $passwordArgument) {
            $output->writeln('<error>The password argument must be a non-empty string.</error>');

            return Command::INVALID;
        }

        /** @var non-empty-string $identifier */
        $identifier = $identifierArgument;
        $password = $passwordArgument;
        $displayName = is_string($displayNameOption) && '' !== trim($displayNameOption) ? $displayNameOption : ucfirst($identifier);
        $email = is_string($emailOption) && '' !== trim($emailOption) ? $emailOption : null;

        if (!is_array($rolesOption) || [] === $rolesOption) {
            $rolesOption = ['ROLE_APPLICATION_VIEWER'];
        }

        /** @var list<string> $normalizedRoles */
        $normalizedRoles = array_values(array_filter(
            array_map(static fn (mixed $value): string => is_string($value) ? $value : '', $rolesOption),
            static fn (string $role): bool => '' !== trim($role)
        ));

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
}
