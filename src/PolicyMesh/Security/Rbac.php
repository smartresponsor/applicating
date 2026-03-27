<?php

declare(strict_types=1);

namespace App\Component\Product\PolicyMesh\Security;

final class Rbac
{
    /** @var array<string,string[]> */
    private array $roles = [
        'region_admin' => ['mesh.propose', 'mesh.vote', 'mesh.finalize', 'mesh.read'],
        'auditor' => ['mesh.read'],
        'observer' => ['mesh.read'],
    ];

    public function allow(string $role, string $perm): bool
    {
        return in_array($perm, $this->roles[$role] ?? [], true);
    }
}
