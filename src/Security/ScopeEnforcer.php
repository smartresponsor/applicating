<?php

declare(strict_types=1);

namespace App\Component\Product\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class ScopeEnforcer
{
    /** @param array<string,string[]> $matrix */
    public function __construct(private readonly array $matrix)
    {
    }

    public function __invoke(RequestEvent $event): void
    {
        $r = $event->getRequest();
        $scopes = (array) ($r->attributes->get('scopes') ?? []);
        $ruleKey = strtoupper($r->getMethod()).' '.$r->getPathInfo();

        foreach ($this->matrix as $pattern => $required) {
            $regex = '#^'.str_replace(['*'], ['.*'], $pattern).'$#';
            if (preg_match($regex, $ruleKey)) {
                foreach ($required as $req) {
                    if (!in_array($req, $scopes, true)) {
                        $event->setResponse(new JsonResponse(['error' => 'insufficient_scope', 'required' => $required], 403));

                        return;
                    }
                }
                break;
            }
        }
    }
}
