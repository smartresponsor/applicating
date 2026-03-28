<?php

declare(strict_types=1);

namespace App\Command\Analytics;

use App\Analytics\Product\ProductAnalyticsAggregator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'analytics:rebuild', description: 'Rebuild product analytics daily aggregates')] final class AnalyticsRebuildCommand extends Command
{
    public function __construct(private ProductAnalyticsAggregator $agg)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('from', InputArgument::REQUIRED, 'YYYY-MM-DD');
        $this->addArgument('to', InputArgument::REQUIRED, 'YYYY-MM-DD');
        $this->addOption('tenant', null, InputOption::VALUE_REQUIRED, 'Tenant ID');
        $this->addOption('product', null, InputOption::VALUE_REQUIRED, 'Product ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $from = new \DateTimeImmutable((string) $input->getArgument('from'));
        $to = new \DateTimeImmutable((string) $input->getArgument('to'));
        $tenant = $input->getOption('tenant') ? (string) $input->getOption('tenant') : null;
        $product = $input->getOption('product') ? (int) $input->getOption('product') : null;
        $n = $this->agg->rebuild($from, $to, $tenant, $product);
        $output->writeln("<info>Rebuilt {$n} daily rows.</info>");

        return Command::SUCCESS;
    }
}
