<?php

declare(strict_types=1);

namespace App\Component\Product\Observability\Tracing;

use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

final class HttpTracingMiddleware
{
    private $span;

    public function onRequest(RequestEvent $event): void
    {
        $req = $event->getRequest();
        $tp = Globals::tracerProvider();
        $tracer = $tp->getTracer('catalog-http');
        $this->span = $tracer->spanBuilder($req->getMethod().' '.$req->getPathInfo())
            ->setSpanKind(SpanKind::KIND_SERVER)
            ->startSpan();
        $this->span->setAttribute('http.method', $req->getMethod());
        $this->span->setAttribute('http.target', $req->getPathInfo());
        $this->span->setAttribute('http.client_ip', $req->getClientIp() ?: '');
    }

    public function onResponse(ResponseEvent $event): void
    {
        if (null === $this->span) {
            return;
        }
        $res = $event->getResponse();
        $this->span->setAttribute('http.status_code', $res->getStatusCode());
        if ($res->getStatusCode() >= 500) {
            $this->span->setStatus(StatusCode::STATUS_ERROR);
        }
        $this->span->end();
        $this->span = null;
    }
}
