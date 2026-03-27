<?php

declare(strict_types=1);

namespace App\Component\Product\Observability\Tracing;

use OpenTelemetry\API\Globals;

final class DbTracing
{
    /** Simple helper to wrap query execution with a span */
    public static function trace(string $operation, callable $runner)
    {
        $tracer = Globals::tracerProvider()->getTracer('catalog-db');
        $span = $tracer->spanBuilder('db.'.$operation)->startSpan();
        try {
            $res = $runner();
            $span->end();

            return $res;
        } catch (\Throwable $e) {
            $span->recordException($e);
            $span->end();
            throw $e;
        }
    }
}
