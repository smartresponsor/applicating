<?php

declare(strict_types=1);

namespace App\Applicating\Command;

use App\Applicating\Repository\ApplicationFixtureRepository;
use Doctrine\Bundle\FixturesBundle\Loader\SymfonyFixturesLoader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'applicating:fixtures:load-demo', description: 'Load demo application lifecycle fixtures')]
/**
 * Loads the canonical demo lifecycle fixture set through the repository fixture boundary.
 */
final class ApplicationFixturesLoadDemoCommand extends Command
{
    public function __construct(
        private readonly ApplicationFixtureRepository $applicationFixtureRepository,
        private readonly ?SymfonyFixturesLoader $loader = null,
    ) {
        parent::__construct();
    }

    /**
     * Loads all registered fixtures and fails explicitly when the fixture loader is unavailable.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->loader instanceof SymfonyFixturesLoader) {
            throw new \RuntimeException('Doctrine fixtures loader is not available in this environment.');
        }

        $this->applicationFixtureRepository->loadFixtures($this->loader->getFixtures());

        $output->writeln('<info>Demo fixtures loaded.</info>');

        return Command::SUCCESS;
    }
}
