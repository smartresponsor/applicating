<?php

declare(strict_types=1);

namespace App\Component\Product\Security\Admin;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class AdminAccessVoter
{
    /** @var array<string,string[]> */
    public function __construct(private readonly array $rules)
    {
    }

    public function __invoke(RequestEvent $event): void
    {
        $req = $event->getRequest();
        if (str_starts_with($req->getPathInfo(), '/api/admin/auth/')) {
            return;
        } // allow login
        $auth = (string) ($req->headers->get('Authorization') ?? '');
        if (!str_starts_with($auth, 'Bearer ')) {
            $event->setResponse(new JsonResponse(['error' => 'unauthorized'], 401));

            return;
        }
        $payload = json_decode(base64_decode(substr($auth, 7)) ?: '{}', true) ?: [];
        $role = (string) ($payload['role'] ?? '');
        $ruleKey = $req->getMethod().' '.$req->getPathInfo();
        foreach ($this->rules as $pattern => $roles) {
            $re = '#^' + str_replace('*', '.*', $pattern) + '$#';
            if (@preg_match($re, $ruleKey)) {
                if (!in_array($role, $roles, true)) {
                    $event->setResponse(new JsonResponse(['error' => 'forbidden'], 403));
                }

                return;
            }
        }
    }
}
