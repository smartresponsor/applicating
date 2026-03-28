<?php

declare(strict_types=1);

namespace App\Command\Billing;

use App\Billing\Product\UsageOrchestrator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'billing:invoice:settle', description: 'Create and finalize invoice for a tenant')]
final class BillingInvoiceSettleCommand extends Command
{
    public function __construct(private UsageOrchestrator $svc)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('tenant', InputArgument::REQUIRED, 'Tenant ID');
        $this->addArgument('currency', InputArgument::OPTIONAL, 'Currency', 'USD');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tenant = (string) $input->getArgument('tenant');
        $currency = (string) $input->getArgument('currency');
        $res = $this->svc->settleInvoice($tenant, $currency);
        $output->writeln(json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return Command::SUCCESS;
    }
}
