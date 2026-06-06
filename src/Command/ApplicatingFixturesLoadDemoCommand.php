<?php

declare(strict_types=1);

namespace App\Applicating\Command;

use Doctrine\Bundle\FixturesBundle\Loader\SymfonyFixturesLoader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'applicating:fixtures:load-demo', description: 'Load demo application lifecycle fixtures')]
final class ApplicatingFixturesLoadDemoCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ?SymfonyFixturesLoader $loader = null,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->loader instanceof SymfonyFixturesLoader) {
            throw new \RuntimeException('Doctrine fixtures loader is not available in this environment.');
        }

        $executor = new ORMExecutor($this->entityManager, new ORMPurger());
        $executor->execute($this->loader->getFixtures(), true);

        $output->writeln('<info>Demo fixtures loaded.</info>');

        return Command::SUCCESS;
    }
}
