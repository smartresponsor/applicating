<?php
declare(strict_types=1);

namespace App\Component\Product\Commands;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'audit:prune', description: 'Prune audit_log older than N days')]
final class AuditPruneCommand extends Command
{
    public function __construct(private readonly Connection $db) { parent::__construct(); }

    protected function configure(): void
    {
        $this->addArgument('days', InputArgument::OPTIONAL, 'Retention days', '90');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $days = (int)$input->getArgument('days');
        $cutoff = (new \DateTimeImmutable("-{$days} days"))->format('Y-m-d H:i:s');
        $affected = $this->db->executeStatement('DELETE FROM audit_log WHERE occurred_at < :cutoff', ['cutoff' => $cutoff]);
        $output->writeln("Pruned {$affected} rows older than {$days} days");
        return Command::SUCCESS;
    }
}
