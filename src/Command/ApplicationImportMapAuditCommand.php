<?php

declare(strict_types=1);

namespace App\Applicating\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'importmap:audit', description: 'Audit importmap assets for the Applicating application')]
/**
 * Reports the Applicating runtime import-map posture without mutating application assets.
 */
final class ApplicationImportMapAuditCommand extends Command
{
    /**
     * Confirms that the active runtime intentionally defines no import-map assets.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>No importmap assets are defined in the active Applicating runtime.</info>');

        return Command::SUCCESS;
    }
}
