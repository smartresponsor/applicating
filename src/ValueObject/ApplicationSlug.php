<?php

declare(strict_types=1);

namespace App\ValueObject;

final readonly class ApplicationSlug
{
    public function __construct(private string $value)
    {
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value)) {
            throw new \InvalidArgumentException('Application slug must use lowercase kebab-case.');
        }
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
