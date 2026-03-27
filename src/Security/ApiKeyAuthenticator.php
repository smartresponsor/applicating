<?php

declare(strict_types=1);

namespace App\Component\Product\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class ApiKeyAuthenticator
{
    public function __construct(private readonly ApiKeyRepository $repo)
    {
    }

    public function __invoke(RequestEvent $event): void
    {
        $r = $event->getRequest();
        $tenant = (string) ($r->headers->get('X-Tenant-Id') ?? '');
        $key = (string) ($r->headers->get('X-API-Key') ?? '');
        if ('' === $tenant || '' === $key) {
            return;
        }
        $row = $this->repo->findByKey($tenant, $key);
        if (!$row) {
            $event->setResponse(new JsonResponse(['error' => 'invalid_api_key'], 401));

            return;
        }
        $r->attributes->set('tenant', $tenant);
        $r->attributes->set('roles', $row['roles']);
        $r->attributes->set('scopes', $row['scopes']);
        $r->attributes->set('api_key_row', $row);
    }
}
