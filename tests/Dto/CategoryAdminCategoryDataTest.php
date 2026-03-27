<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Dto;

use App\Dto\CategoryAdminCategoryData;
use PHPUnit\Framework\TestCase;

final class CategoryAdminCategoryDataTest extends TestCase
{
    public function testFromArrayNormalizesScalarValues(): void
    {
        $dto = CategoryAdminCategoryData::fromArray([
            'name' => '  Summer Collection  ',
            'slug' => '  summer-collection  ',
        ]);

        self::assertSame('Summer Collection', $dto->name);
        self::assertSame('summer-collection', $dto->slug);
    }

    public function testFromArrayFallsBackToEmptyStringsForInvalidValues(): void
    {
        $dto = CategoryAdminCategoryData::fromArray([
            'name' => ['not', 'scalar'],
            'slug' => null,
        ]);

        self::assertSame('', $dto->name);
        self::assertSame('', $dto->slug);
    }
}
