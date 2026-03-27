<?php
declare(strict_types=1);

namespace App\Component\Product\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'product_category')]
class ProductCategory
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'guid')]
    private string $id;

    #[ORM\Column(name: 'category_slug', type: 'string', length: 128, unique: true)]
    private string $slug;

    #[ORM\Column(name: 'category_title', type: 'string', length: 255)]
    private string $title;

    #[ORM\ManyToOne(targetEntity: ProductCategory::class)]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?ProductCategory $parent = null;

    public function __construct(string $slug, string $title, ?ProductCategory $parent = null)
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->slug = strtolower(trim($slug));
        $this->title = $title;
        $this->parent = $parent;
    }

    public function id(): string { return $this->id; }
    public function slug(): string { return $this->slug; }
    public function title(): string { return $this->title; }
    public function parent(): ?ProductCategory { return $this->parent; }
}
