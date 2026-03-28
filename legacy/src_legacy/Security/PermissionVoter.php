<?php

declare(strict_types=1);

namespace App\Component\Product\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class PermissionVoter
{
    public function __construct(private readonly array $matrix)
    {
    }

    public function __invoke(RequestEvent $event): void
    {
        $r = $event->getRequest();
        $roles = $r->attributes->get('roles') ?? [];
        $path = $r->getPathInfo();
        $method = strtoupper($r->getMethod());
        $ruleKey = $method.' '.$path;
        $allowed = $this->match($ruleKey, (array) $roles);
        if (!$allowed) {
            $event->setResponse(new JsonResponse(['error' => 'forbidden'], 403));
        }
    }

    private function match(string $ruleKey, array $roles): bool
    {
        foreach ($this->matrix as $pattern => $allowedRoles) {
            $regex = '#^'.str_replace(['*'], ['.*'], $pattern).'$#';
            if (preg_match($regex, $ruleKey)) {
                foreach ($roles as $role) {
                    if (in_array((string) $role, $allowedRoles, true)) {
                        return true;
                    }
                }

                return false;
            }
        }

        return true;
    }
}
