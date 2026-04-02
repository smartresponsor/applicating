<?php

declare(strict_types=1);

namespace App\Command;

use App\ServiceInterface\ApplicationReadinessServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'applicating:application:evaluate-readiness', description: 'Run readiness evaluation scenarios')]
final class ApplicatingApplicationEvaluateReadinessCommand extends Command
{
    public function __construct(
        private readonly ApplicationReadinessServiceInterface $readinessService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $root = dirname(__DIR__, 2);
        $scenarios = require $root . '/tools/evaluation/application_readiness_scenarios.php';
        require_once $root . '/tools/evaluation/ApplicationReadinessEvaluator.php';

        $evaluator = new \ApplicationReadinessEvaluator();

        $failed = 0;

        foreach ($scenarios as $scenario) {
            $readiness = $this->readinessService->buildReadiness($scenario['slug']);
            $result = $evaluator->evaluate($scenario, $readiness);

            if ($result['passed']) {
                $output->writeln(sprintf('<info>[PASS]</info> %s', $scenario['name']));
            } else {
                $failed++;
                $output->writeln(sprintf('<error>[FAIL]</error> %s', $scenario['name']));
                foreach ($result['mismatches'] as $mismatch) {
                    $output->writeln('  - ' . $mismatch);
                }
            }
        }

        return $failed === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
