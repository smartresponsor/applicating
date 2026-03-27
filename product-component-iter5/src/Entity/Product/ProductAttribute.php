<?php
declare(strict_types=1);

namespace App\Component\Product\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'product_attribute')]
class ProductAttribute
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'guid')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Product $product;

    #[ORM\Column(name: 'attr_code', type: 'string', length: 64)]
    private string $code; // e.g. material

    #[ORM\Column(name: 'attr_value', type: 'string', length: 255)]
    private string $value;

    public function __construct(Product $product, string $code, string $value)
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->product = $product;
        $this->code = strtolower(trim($code));
        $this->value = $value;
    }

    public function id(): string { return $this->id; }
    public function code(): string { return $this->code; }
    public function value(): string { return $this->value; }
    public function product(): Product { return $this->product; }
}
