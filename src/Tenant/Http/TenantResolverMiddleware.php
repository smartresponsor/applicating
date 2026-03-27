<?php

declare(strict_types=1);

namespace App\Component\Product\Tenant\Http;

use App\Component\Product\Tenant\TenantContext;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class TenantResolverMiddleware
{
    public function __construct(private readonly TenantContext $ctx, private readonly array $options = [])
    {
    }

    public function __invoke(RequestEvent $event): void
    {
        $r = $event->getRequest();
        $tenant = (string) ($r->headers->get('X-Tenant-Id') ?? $r->query->get('tenant') ?? 'public');
        $mode = (string) ($this->options['mode'] ?? 'rls');
        $this->ctx->id = $tenant;
        $this->ctx->mode = $mode;
        // прокинем в атрибуты запроса
        $r->attributes->set('tenant_id', $tenant);
    }
}
