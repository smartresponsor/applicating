<?php
declare(strict_types=1);

namespace App\Component\Product\Command\Product;

use App\Component\Product\Outbox\Product\ProductOutbox;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'product:outbox:relay', description: 'Relay product outbox events to message bus')]
final class OutboxRelayCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repo = $this->em->getRepository(ProductOutbox::class);
        $pending = $repo->createQueryBuilder('o')
            ->andWhere('o.processedAt IS NULL')
            ->setMaxResults(1000)
            ->getQuery()->getResult();

        foreach ($pending as $record) {
            // Здесь можно отправить в Kafka/RabbitMQ; пока просто помечаем обработанным
            $record->markProcessed();
            $output->writeln('Relayed: ' . $record->eventName() . ' [' . $record->id() . ']');
        }
        $this->em->flush();
        return Command::SUCCESS;
    }
}
