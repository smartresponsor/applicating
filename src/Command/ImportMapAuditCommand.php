<?php

declare(strict_types=1);

namespace App\Applicating\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'importmap:audit', description: 'Audit importmap assets for the Applicating application')]
final class ImportMapAuditCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>No importmap assets are defined in the active Applicating runtime.</info>');

        return Command::SUCCESS;
    }
}
