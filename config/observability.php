<?php

declare(strict_types=1);

// config/observability.php
return [
    'otel' => [
        'endpoint' => getenv('OTEL_EXPORTER_OTLP_ENDPOINT') ?: 'http://localhost:4318',
        'sampler_ratio' => (float) (getenv('OTEL_TRACES_SAMPLER_RATIO') ?: '1.0'),
    ],
    'logging' => [
        'otlp_endpoint' => getenv('OTEL_EXPORTER_OTLP_LOGS_ENDPOINT') ?: (getenv('OTEL_EXPORTER_OTLP_ENDPOINT') ? getenv('OTEL_EXPORTER_OTLP_ENDPOINT').'/v1/logs' : 'http://localhost:4318/v1/logs'),
    ],
];
