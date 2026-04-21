<?php

declare(strict_types=1);

namespace App\Application\ValueObject;

final readonly class ApplicationManifestIdentifier
{
    public function __construct(private string $value)
    {
        if (!preg_match('/^[a-z0-9]+(?:\.[a-z0-9-]+)+$/', $value)) {
            throw new \InvalidArgumentException('Manifest identifier must use reverse-domain notation.');
        }
    }

    public function toString(): string
    {
        return $this->value;
    }
}
