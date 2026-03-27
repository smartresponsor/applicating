<?php
declare(strict_types=1);

namespace App\Component\Product\Enum;

enum ProductStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case ARCHIVED = 'archived';
}
