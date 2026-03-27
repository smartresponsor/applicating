<?php

declare(strict_types=1);

namespace App\Component\Product\Security\Admin;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class AdminJwtController
{
    // stub login: returns unsigned demo token for UI wiring
    public function login(Request $r): JsonResponse
    {
        $email = (string) ($r->toArray()['email'] ?? '');
        $role = 'admin@example.com' === $email ? 'ROLE_SUPERADMIN' : 'ROLE_TENANT_OWNER';
        $token = base64_encode(json_encode(['sub' => $email, 'role' => $role, 'iat' => time(), 'exp' => time() + 3600]));

        return new JsonResponse(['token' => $token, 'role' => $role]);
    }
}
