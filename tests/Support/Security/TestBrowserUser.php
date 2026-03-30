<?php

declare(strict_types=1);

namespace App\Tests\Support\Security;

use Symfony\Component\Security\Core\User\UserInterface;

final class TestBrowserUser implements UserInterface
{
    /** @param list<string> $roles */
    public function __construct(
        private readonly string $identifier,
        private readonly array $roles,
    ) {
    }

    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }

    /** @return list<string> */
    public function getRoles(): array
    {
        return array_values(array_unique($this->roles));
    }

    public function eraseCredentials(): void
    {
    }
}
