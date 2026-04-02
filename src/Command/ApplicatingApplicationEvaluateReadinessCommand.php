<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application as FrameworkApplication;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'applicating:application:evaluate-readiness', description: 'Run readiness evaluation scenarios')]
final class ApplicatingApplicationEvaluateReadinessCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('min-score', null, InputOption::VALUE_REQUIRED, 'Minimum passing score between 0 and 1.', '1.0');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $minScore = max(0.0, min(1.0, (float) $input->getOption('min-score')));

        $root = dirname(__DIR__, 2);
        $script = $root . '/tools/evaluation/run_readiness_evaluation.php';

        if (!is_file($script)) {
            $output->writeln('<error>Evaluation runner script is missing.</error>');

            return Command::FAILURE;
        }

        $_SERVER['APP_READINESS_EVAL_MIN_SCORE'] = (string) $minScore;
        $_ENV['APP_READINESS_EVAL_MIN_SCORE'] = (string) $minScore;

        $exitCode = (static function (string $scriptPath): int {
            require $scriptPath;

            return 0;
        })($script);

        $reportPath = $root . '/report/evaluation/application_readiness_evaluation.json';
        if (!is_file($reportPath)) {
            $output->writeln('<error>Evaluation report was not produced.</error>');

            return Command::FAILURE;
        }

        $report = json_decode((string) file_get_contents($reportPath), true, 512, JSON_THROW_ON_ERROR);
        $summary = $report['summary'] ?? [];

        $output->writeln(sprintf('<info>Evaluation total:</info> %d', (int) ($summary['total'] ?? 0)));
        $output->writeln(sprintf('<info>Passed:</info> %d', (int) ($summary['passed'] ?? 0)));
        $output->writeln(sprintf('<info>Failed:</info> %d', (int) ($summary['failed'] ?? 0)));
        $output->writeln(sprintf('<info>Score:</info> %.3f', (float) ($summary['score'] ?? 0.0)));
        $output->writeln(sprintf('<info>Min score:</info> %.3f', $minScore));

        foreach ($report['results'] ?? [] as $result) {
            if (($result['passed'] ?? false) === true) {
                $output->writeln(sprintf('<info>[PASS]</info> %s', (string) ($result['scenario'] ?? 'unknown')));
                continue;
            }

            $output->writeln(sprintf('<error>[FAIL]</error> %s', (string) ($result['scenario'] ?? 'unknown')));
            foreach ($result['mismatches'] ?? [] as $mismatch) {
                $output->writeln('  - ' . (string) $mismatch);
            }
        }

        $thresholdPassed = ($summary['thresholdPassed'] ?? false) === true;

        return $thresholdPassed ? FrameworkApplication::SUCCESS : FrameworkApplication::FAILURE;
    }
}
