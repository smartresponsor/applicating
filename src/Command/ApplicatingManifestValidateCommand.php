<?php

declare(strict_types=1);

namespace App\Command;

use App\DTO\Application\ApplicationManifestData;
use App\ServiceInterface\ApplicationManifestServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'applicating:manifest:validate', description: 'Validate application manifest semantics')]
final class ApplicatingManifestValidateCommand extends Command
{
    public function __construct(private readonly ApplicationManifestServiceInterface $applicationManifestService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('identifier', InputArgument::REQUIRED);
        $this->addArgument('capabilities', InputArgument::OPTIONAL, 'Comma separated capabilities', 'listing,reporting');
    }

    /**
     * @throws \JsonException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $identifier = $input->getArgument('identifier');
        $capabilities = $input->getArgument('capabilities');
        if (!is_string($identifier) || '' === $identifier || !is_string($capabilities)) {
            $output->writeln('<error>Manifest arguments are invalid.</error>');

            return Command::INVALID;
        }

        $data = new ApplicationManifestData();
        $data->identifier = $identifier;
        $data->capabilities = implode("\n", explode(',', $capabilities));
        $payload = $this->applicationManifestService->normalizeManifestPayload($data);

        $output->writeln('<info>Manifest payload normalized.</info>');
        $output->writeln(json_encode($payload, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        return Command::SUCCESS;
    }
}
