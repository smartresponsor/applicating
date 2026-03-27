<?php

declare(strict_types=1);

namespace App\Command\Billing;

use App\Billing\Product\UsageOrchestrator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'billing:usage:import', description: 'Import usage from CSV: tenantId,productId,units,currency,traceId')]
final class BillingUsageImportCommand extends Command
{
    public function __construct(private UsageOrchestrator $svc)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('csv', InputArgument::REQUIRED, 'CSV file path');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $csv = (string) $input->getArgument('csv');
        if (!is_readable($csv)) {
            $output->writeln('<error>File not readable</error>');

            return Command::INVALID;
        }
        $h = fopen($csv, 'r');
        $cnt = 0;
        while (($row = fgetcsv($h)) !== false) {
            if (count($row) < 3) {
                continue;
            }
            [$tenant, $productId, $units, $currency, $trace] = array_pad($row, 5, null);
            $this->svc->trackUsage((string) $tenant, (int) $productId, (float) $units, $currency ?: 'USD', $trace ?: null);
            ++$cnt;
        }
        fclose($h);
        $output->writeln("<info>Imported {$cnt} rows.</info>");

        return Command::SUCCESS;
    }
}
