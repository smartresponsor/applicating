<?php
declare(strict_types=1);

namespace App\Component\Product\Http\Product\Subscriber;

use App\Component\Product\Http\Product\Event\ApiErrorEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

interface TraceIdProviderInterface { public function next(): string; }

final class SimpleTraceIdProvider implements TraceIdProviderInterface
{
    public function next(): string { return bin2hex(random_bytes(8)); }
}

final class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly EventDispatcherInterface $events, private readonly TraceIdProviderInterface $trace) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onException'];
    }

    public function onException(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();
        $status = $e instanceof \InvalidArgumentException ? 400 : ($e instanceof \RuntimeException ? 404 : 500);
        $traceId = $this->trace->next();

        $payload = [
            'error' => [
                'type' => (new \ReflectionClass($e))->getShortName(),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace_id' => $traceId
            ]
        ];
        $event->setResponse(new JsonResponse($payload, $status));
        $this->events->dispatch(new ApiErrorEvent($event->getRequest()->getPathInfo(), $payload['error']['type'], $e->getMessage(), $status));
    }
}
