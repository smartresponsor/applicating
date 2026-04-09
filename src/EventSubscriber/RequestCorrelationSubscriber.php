<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RequestCorrelationSubscriber implements EventSubscriberInterface
{
    public const string ATTRIBUTE = '_applicating_request_id';
    private const string HEADER = 'X-Request-Id';

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
            KernelEvents::RESPONSE => 'onResponse',
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $requestId = $request->headers->get(self::HEADER);

        if (!is_string($requestId) || '' === trim($requestId)) {
            $requestId = bin2hex(random_bytes(16));
        }

        $request->attributes->set(self::ATTRIBUTE, $requestId);
    }

    public function onResponse(ResponseEvent $event): void
    {
        $requestId = $event->getRequest()->attributes->get(self::ATTRIBUTE);

        if (is_string($requestId) && '' !== $requestId) {
            $event->getResponse()->headers->set(self::HEADER, $requestId);
        }
    }
}
