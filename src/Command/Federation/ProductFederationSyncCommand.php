<?php

declare(strict_types=1);

namespace App\Command\Federation;

use App\Federation\Product\ProductFederationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'product:federation:sync', description: 'Sync federation context for product(s)')] final class ProductFederationSyncCommand extends Command
{
    public function __construct(private ProductFederationService $svc)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('product', null, InputOption::VALUE_REQUIRED, 'Product ID')->addOption('all', null, InputOption::VALUE_NONE, 'Sync all');
    }

    protected function execute(InputInterface $in, OutputInterface $out): int
    {
        $pid = $in->getOption('product');
        $all = (bool) $in->getOption('all');
        if ($all) {
            $out->writeln('<comment>--all не реализован. Используйте --product=ID.</comment>');

            return Command::INVALID;
        } if (!$pid) {
            $out->writeln('<error>Укажите --product=ID</error>');

            return Command::INVALID;
        } $res = $this->svc->sync((int) $pid);
        $out->writeln(json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return Command::SUCCESS;
    }
}
