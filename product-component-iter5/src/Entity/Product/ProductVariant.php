<?php
declare(strict_types=1);

namespace App\Component\Product\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'product_variant')]
class ProductVariant
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'guid')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Product $product;

    #[ORM\Column(name: 'variant_sku', type: 'string', length: 64, unique: true)]
    private string $variantSku;

    #[ORM\Column(name: 'variant_price_amount', type: 'integer')]
    private int $priceAmount;

    #[ORM\Column(name: 'variant_price_currency', type: 'string', length: 3)]
    private string $priceCurrency;

    #[ORM\Column(name: 'variant_stock', type: 'integer')]
    private int $stock = 0;

    public function __construct(Product $product, string $variantSku, int $priceAmount, string $priceCurrency, int $stock = 0)
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->product = $product;
        $this->variantSku = strtoupper($variantSku);
        $this->priceAmount = $priceAmount;
        $this->priceCurrency = $priceCurrency;
        $this->stock = max(0, $stock);
    }

    public function id(): string { return $this->id; }
    public function product(): Product { return $this->product; }
    public function variantSku(): string { return $this->variantSku; }
    public function priceAmount(): int { return $this->priceAmount; }
    public function priceCurrency(): string { return $this->priceCurrency; }
    public function stock(): int { return $this->stock; }
}
