<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Tests\Unit\EventSubscriber;

use App\Applicating\EventSubscriber\ApplicationRequestCorrelationSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class ApplicationRequestCorrelationSubscriberTest extends TestCase
{
    public function testSubscribedEventsExposeRequestAndResponseHooks(): void
    {
        self::assertSame(
            [
                KernelEvents::REQUEST => 'onRequest',
                KernelEvents::RESPONSE => 'onResponse',
            ],
            ApplicationRequestCorrelationSubscriber::getSubscribedEvents(),
        );
    }

    public function testOnRequestPreservesProvidedRequestId(): void
    {
        $request = Request::create('/health');
        $request->headers->set('X-Request-Id', 'request-123');

        $subscriber = new ApplicationRequestCorrelationSubscriber();
        $subscriber->onRequest(new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        ));

        self::assertSame(
            'request-123',
            $request->attributes->get(ApplicationRequestCorrelationSubscriber::ATTRIBUTE),
        );
    }

    public function testOnRequestGeneratesRequestIdWhenHeaderIsBlank(): void
    {
        $request = Request::create('/health');
        $request->headers->set('X-Request-Id', '   ');

        $subscriber = new ApplicationRequestCorrelationSubscriber();
        $subscriber->onRequest(new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        ));

        $requestId = $request->attributes->get(ApplicationRequestCorrelationSubscriber::ATTRIBUTE);

        self::assertIsString($requestId);
        self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $requestId);
    }

    public function testOnResponseCopiesRequestIdToResponseHeader(): void
    {
        $request = Request::create('/health');
        $request->attributes->set(ApplicationRequestCorrelationSubscriber::ATTRIBUTE, 'request-456');
        $response = new Response();

        $subscriber = new ApplicationRequestCorrelationSubscriber();
        $subscriber->onResponse(new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response,
        ));

        self::assertSame('request-456', $response->headers->get('X-Request-Id'));
    }

    public function testOnResponseDoesNotAddHeaderWithoutValidRequestId(): void
    {
        $request = Request::create('/health');
        $response = new Response();

        $subscriber = new ApplicationRequestCorrelationSubscriber();
        $subscriber->onResponse(new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response,
        ));

        self::assertFalse($response->headers->has('X-Request-Id'));
    }
}
