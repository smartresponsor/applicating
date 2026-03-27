<?php
declare(strict_types=1);

namespace App\Component\Product\Entity\Product;

use App\Component\Product\Enum\ProductStatus;
use App\Component\Product\ValueObject\Product\Sku;
use App\Component\Product\ValueObject\Product\Money;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: 'App\\Component\\Product\\Repository\\Product\\ProductRepository')]
#[ORM\Table(name: 'product')]
class Product implements \App\Component\Product\Interface\Product\ProductInterface
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'guid', unique: true)]
    private string $id;

    #[ORM\Embedded(class: Sku::class, columnPrefix: 'product_sku_')]
    private Sku $sku;

    #[ORM\Embedded(class: Money::class, columnPrefix: 'product_price_')]
    private Money $price;

    #[ORM\Column(name: 'product_stock', type: 'integer')]
    private int $stock = 0;

    #[ORM\Column(name: 'product_status', type: 'string', length: 32)]
    private string $status;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, ProductI18n> */
    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductI18n::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $translations;

    public function __construct(Sku $sku, Money $price, int $initialStock = 0, ProductStatus $status = ProductStatus::DRAFT)
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->sku = $sku;
        $this->price = $price;
        $this->stock = max(0, $initialStock);
        $this->status = $status->value;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
        $this->translations = new ArrayCollection();
    }

    public function id(): string { return $this->id; }
    public function sku(): Sku { return $this->sku; }
    public function price(): Money { return $this->price; }
    public function stock(): int { return $this->stock; }
    public function status(): ProductStatus { return ProductStatus::from($this->status); }
    public function createdAt(): \DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    /** @return Collection<int, ProductI18n> */
    public function translations(): Collection { return $this->translations; }

    public function addTranslation(ProductI18n $i18n): void
    {
        foreach ($this->translations as $t) {
            if ($t->locale() === $i18n->locale()) {
                throw new \DomainException('Translation for locale already exists');
            }
        }
        $this->translations->add($i18n);
        $this->touch();
    }

    public function changePrice(Money $newPrice): void { $this->price = $newPrice; $this->touch(); }
    public function adjustStock(int $delta): void
    {
        $new = $this->stock + $delta;
        if ($new < 0) throw new \DomainException('Stock cannot be negative');
        $this->stock = $new; $this->touch();
    }
    public function activate(): void { $this->status = ProductStatus::ACTIVE->value; $this->touch(); }
    public function archive(): void { $this->status = ProductStatus::ARCHIVED->value; $this->touch(); }
    private function touch(): void { $this->updatedAt = new \DateTimeImmutable(); }
}
