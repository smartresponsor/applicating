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
        $this
            ->addOption('min-score', null, InputOption::VALUE_REQUIRED, 'Minimum passing score between 0 and 1.', '1.0')
            ->addOption('profile', null, InputOption::VALUE_REQUIRED, 'Evaluation profile: strict|soft|dev', 'strict');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $minScore = max(0.0, min(1.0, (float) $input->getOption('min-score')));
        $profile = (string) $input->getOption('profile');

        $root = dirname(__DIR__, 2);
        $script = $root . '/tools/evaluation/run_readiness_evaluation.php';

        $_ENV['APP_READINESS_EVAL_MIN_SCORE'] = (string) $minScore;
        $_ENV['APP_READINESS_EVAL_PROFILE'] = $profile;

        require $script;

        $reportPath = $root . '/report/evaluation/application_readiness_evaluation.json';
        $report = json_decode((string) file_get_contents($reportPath), true);

        $policy = $report['policy'] ?? [];

        $output->writeln(sprintf('<info>Profile:</info> %s', $policy['profile'] ?? 'unknown'));
        $output->writeln(sprintf('<info>Should fail:</info> %s', ($policy['shouldFail'] ?? false) ? 'true' : 'false'));

        if (($policy['failedByRegression'] ?? false) === true) {
            $output->writeln('<error>Regression detected</error>');
        }

        return ($policy['shouldFail'] ?? false) ? FrameworkApplication::FAILURE : FrameworkApplication::SUCCESS;
    }
}
