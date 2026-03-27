<?php
declare(strict_types=1);

namespace App\Component\Product\Entity\Product;

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
    #[ORM\Column(name: 'locale', type: 'string', length: 5)]
    private string $locale;

    #[ORM\Column(name: 'product_title', type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(name: 'product_slug', type: 'string', length: 255)]
    private string $slug;

    #[ORM\Column(name: 'product_description', type: 'text', nullable: true)]
    private ?string $description = null;

    public function __construct(Product $product, string $locale, string $name, string $slug, ?string $description = null)
    {
        $this->product = $product;
        $this->locale = $locale;
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
    }
}
