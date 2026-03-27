<?php

declare(strict_types=1);

namespace App\Component\Product\Security;

enum Roles: string
{
    case ADMIN = 'ADMIN';
    case MANAGER = 'MANAGER';
    case VIEWER = 'VIEWER';
    case SERVICE = 'SERVICE';
}
