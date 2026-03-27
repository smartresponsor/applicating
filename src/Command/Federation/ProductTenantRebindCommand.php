<?php

declare(strict_types=1);

namespace App\Command\Federation;

use App\Federation\Product\ProductFederationService;
use App\Federation\Product\RegionCode;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'product:tenant:rebind', description: 'Rebind product federation context')] final class ProductTenantRebindCommand extends Command
{
    public function __construct(private ProductFederationService $svc)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('product', InputArgument::REQUIRED, 'Product ID')->addOption('federation', null, InputOption::VALUE_REQUIRED, 'Federation ID (URN/UUID)')->addOption('tenant', null, InputOption::VALUE_REQUIRED, 'Tenant ID')->addOption('region', null, InputOption::VALUE_REQUIRED, 'Region (GLOBAL|US|EU|UA|APAC)')->addOption('scope', null, InputOption::VALUE_REQUIRED, 'JSON of scope {env,tags,features,labels}');
    }

    protected function execute(InputInterface $in, OutputInterface $out): int
    {
        $pid = (int) $in->getArgument('product');
        $fid = (string) $in->getOption('federation');
        $ten = (string) $in->getOption('tenant');
        $reg = $in->getOption('region') ? RegionCode::from((string) $in->getOption('region')) : null;
        $scp = $in->getOption('scope') ? json_decode((string) $in->getOption('scope'), true, 512, JSON_THROW_ON_ERROR) : [];
        $this->svc->bind($pid, $fid, $ten, $reg, $scp);
        $out->writeln('<info>Rebound OK</info>');

        return Command::SUCCESS;
    }
}
