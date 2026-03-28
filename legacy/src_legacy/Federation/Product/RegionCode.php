<?php

declare(strict_types=1);

namespace App\Federation\Product;

enum RegionCode: string
{
    case GLOBAL = 'GLOBAL';
    case US = 'US';
    case EU = 'EU';
    case UA = 'UA';
    case APAC = 'APAC';
}
