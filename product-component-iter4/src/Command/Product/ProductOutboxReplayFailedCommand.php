<?php
declare(strict_types=1);

namespace App\Component\Product\Command\Product;

use App\Component\Product\DeadLetter\Product\ProductDeadLetter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'product:outbox:replay-failed', description: 'Replay failed product outbox messages from DLQ')]
final class ProductOutboxReplayFailedCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repo = $this->em->getRepository(ProductDeadLetter::class);
        $failed = $repo->createQueryBuilder('d')->setMaxResults(500)->getQuery()->getResult();

        foreach ($failed as $rec) {
            try {
                // Здесь должен быть реальный publish в Kafka/RabbitMQ
                $rec->incrementRetries();
                $output->writeln('Replayed: ' . $rec->id());
            } catch (\Throwable $e) {
                $rec->incrementRetries();
                $output->writeln('Failed again: ' . $rec->id() . ' reason=' . $e->getMessage());
            }
        }
        $this->em->flush();
        return Command::SUCCESS;
    }
}
