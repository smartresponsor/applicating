<?php
declare(strict_types=1);

namespace App\Component\Product\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'product_option_value')]
class ProductOptionValue
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'guid')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: ProductOption::class)]
    #[ORM\JoinColumn(name: 'option_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ProductOption $option;

    #[ORM\Column(name: 'value', type: 'string', length: 128)]
    private string $value;

    public function __construct(ProductOption $option, string $value)
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->option = $option;
        $this->value = (string)$value;
    }

    public function id(): string { return $this->id; }
    public function option(): ProductOption { return $this->option; }
    public function value(): string { return $this->value; }
}
