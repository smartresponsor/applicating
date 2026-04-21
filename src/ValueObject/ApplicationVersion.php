<?php

declare(strict_types=1);

namespace App\Application\ValueObject;

final readonly class ApplicationVersion
{
    public function __construct(private string $value)
    {
        if (!preg_match('/^\d+\.\d+\.\d+(?:-[a-z0-9.-]+)?$/i', $value)) {
            throw new \InvalidArgumentException('Application version must use semver-like format.');
        }
    }

    public function toString(): string
    {
        return $this->value;
    }
}
