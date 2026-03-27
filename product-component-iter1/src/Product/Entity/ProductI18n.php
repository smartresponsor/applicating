<?php
declare(strict_types=1);

namespace App\Component\Product\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'product_i18n')]
class ProductI18n
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Product $product;

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 5)]
    private string $locale; // en_US, ru_RU, etc.

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255)]
    private string $slug;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    public function __construct(Product $product, string $locale, string $name, string $slug, ?string $description = null)
    {
        $this->product = $product;
        $this->locale = $locale;
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
    }

    public function product(): Product { return $this->product; }
    public function locale(): string { return $this->locale; }
    public function name(): string { return $this->name; }
    public function slug(): string { return $this->slug; }
    public function description(): ?string { return $this->description; }

    public function rename(string $name): void { $this->name = $name; }
    public function changeSlug(string $slug): void { $this->slug = $slug; }
    public function changeDescription(?string $description): void { $this->description = $description; }
}
