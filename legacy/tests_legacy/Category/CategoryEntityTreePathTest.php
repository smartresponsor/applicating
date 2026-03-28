<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Category;

use App\Entity\CategoryEntity;
use PHPUnit\Framework\TestCase;

final class CategoryEntityTreePathTest extends TestCase
{
    public function testGetParentPathReturnsNullForRootNode(): void
    {
        $root = $this->createCategoryEntity('catalog', 0);

        self::assertNull($root->getParentPath());
    }

    public function testGetParentPathReturnsAncestorPath(): void
    {
        $child = $this->createCategoryEntity('catalog.electronics.phones', 2);

        self::assertSame('catalog.electronics', $child->getParentPath());
    }

    public function testIsDirectChildOfChecksDepthAndPath(): void
    {
        $parent = $this->createCategoryEntity('catalog.electronics', 1);
        $child = $this->createCategoryEntity('catalog.electronics.phones', 2);
        $grandChild = $this->createCategoryEntity('catalog.electronics.phones.android', 3);
        $sameDepth = $this->createCategoryEntity('catalog.electronics.laptops', 2);

        self::assertTrue($child->isDirectChildOf($parent));
        self::assertFalse($grandChild->isDirectChildOf($parent));
        self::assertFalse($sameDepth->isDirectChildOf($child));
    }

    private function createCategoryEntity(string $path, int $depth): CategoryEntity
    {
        $reflection = new \ReflectionClass(CategoryEntity::class);
        $entity = $reflection->newInstanceWithoutConstructor();

        $pathProperty = $reflection->getProperty('path');
        $pathProperty->setValue($entity, $path);

        $depthProperty = $reflection->getProperty('depth');
        $depthProperty->setValue($entity, $depth);

        return $entity;
    }
}
