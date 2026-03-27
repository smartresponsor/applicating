<?php
declare(strict_types=1);

namespace App\Component\Product\ReadModel\Product;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'product_read')]
class ProductRead
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'guid')]
    private string $id;

    #[ORM\Column(name: 'product_sku', type: 'string', length: 64, unique: true)]
    private string $sku;

    #[ORM\Column(name: 'product_title', type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(name: 'product_price_amount', type: 'integer')]
    private int $priceAmount;

    #[ORM\Column(name: 'product_price_currency', type: 'string', length: 3)]
    private string $priceCurrency;

    #[ORM\Column(name: 'product_status', type: 'string', length: 32)]
    private string $status;

    #[ORM\Column(name: 'product_stock', type: 'integer')]
    private int $stock;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        string $id,
        string $sku,
        string $title,
        int $priceAmount,
        string $priceCurrency,
        string $status,
        int $stock
    ) {
        $this->id = $id;
        $this->sku = strtoupper($sku);
        $this->title = $title;
        $this->priceAmount = $priceAmount;
        $this->priceCurrency = $priceCurrency;
        $this->status = $status;
        $this->stock = $stock;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function id(): string { return $this->id; }
    public function sku(): string { return $this->sku; }
    public function title(): string { return $this->title; }
    public function priceAmount(): int { return $this->priceAmount; }
    public function priceCurrency(): string { return $this->priceCurrency; }
    public function status(): string { return $this->status; }
    public function stock(): int { return $this->stock; }
    public function updatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    public function refreshUpdatedAt(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
