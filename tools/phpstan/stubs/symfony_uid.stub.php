<?php

declare(strict_types=1);

namespace Symfony\Component\Uid;

final class Ulid
{
    public function __toString(): string
    {
        return '00000000000000000000000000';
    }
}
