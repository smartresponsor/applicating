<?php
declare(strict_types=1);

namespace App\Component\Product\Command\Product;

use App\Component\Product\Entity\Product\Product;
use App\Component\Product\ReadModel\Product\ProductRead;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'product:read:rebuild', description: 'Rebuild product read-model from write-model')]
final class ProductReadRebuildCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $products = $this->em->getRepository(Product::class)->createQueryBuilder('p')->getQuery()->getResult();
        $conn = $this->em->getConnection();
        $conn->executeStatement('TRUNCATE TABLE product_read');

        foreach ($products as $p) {
            $title = '—';
            $read = new ProductRead($p->id(), (string)$p->sku(), $title, $p->price()->amount(), $p->price()->currency(), $p->status()->value, $p->stock());
            $this->em->persist($read);
        }
        $this->em->flush();
        $output->writeln('product_read rebuilt: ' . count($products));
        return Command::SUCCESS;
    }
}
