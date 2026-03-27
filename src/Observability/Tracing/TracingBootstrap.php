<?php

declare(strict_types=1);

namespace App\Component\Product\Observability\Tracing;

use OpenTelemetry\API\Globals;
use OpenTelemetry\Contrib\Otlp\SpanExporter as OtlpSpanExporter;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBased;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

final class TracingBootstrap
{
    private static bool $booted = false;

    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        $endpoint = getenv('OTEL_EXPORTER_OTLP_ENDPOINT') ?: 'http://localhost:4318';
        $ratio = (float) (getenv('OTEL_TRACES_SAMPLER_RATIO') ?: '1.0');

        $exporter = new OtlpSpanExporter(endpoint: $endpoint.'/v1/traces');
        $provider = new TracerProvider(
            new BatchSpanProcessor($exporter),
            new ParentBased(new TraceIdRatioBased($ratio))
        );
        Globals::setTracerProvider($provider);

        self::$booted = true;
    }
}
