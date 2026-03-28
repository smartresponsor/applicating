<?php

declare(strict_types=1);

namespace App\Component\Product\Integration\Symfony;

use App\Component\Product\Observability\Tracing\HttpTracingMiddleware;
use App\Component\Product\Security\ScopeEnforcer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

final class KernelEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly HttpTracingMiddleware $tracer,
        private readonly ScopeEnforcer $scopes,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onRequest', 100],
            ResponseEvent::class => ['onResponse', -100],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        $this->tracer->onRequest($event);
        ($this->scopes)($event);
    }

    public function onResponse(ResponseEvent $event): void
    {
        $this->tracer->onResponse($event);
    }
}
