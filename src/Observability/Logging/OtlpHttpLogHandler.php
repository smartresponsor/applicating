<?php

declare(strict_types=1);

namespace App\Component\Product\Observability\Logging;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

final class OtlpHttpLogHandler extends AbstractProcessingHandler
{
    public function __construct(
        private readonly string $endpoint = 'http://localhost:4318/v1/logs',
    ) {
        parent::__construct();
    }

    protected function write(LogRecord $record): void
    {
        $payload = [
            'resource' => ['attributes' => [['key' => 'service.name', 'value' => ['stringValue' => 'catalog']]]],
            'scopeLogs' => [[
                'scope' => ['name' => 'monolog'],
                'logRecords' => [[
                    'timeUnixNano' => (int) (microtime(true) * 1e9),
                    'severityText' => $record->level->getName(),
                    'body' => ['stringValue' => (string) $record->message],
                    'attributes' => array_map(fn ($k, $v) => ['key' => $k, 'value' => ['stringValue' => is_scalar($v) ? (string) $v : json_encode($v)]], array_keys($record->context), $record->context),
                ]],
            ]],
        ];
        $opts = ['http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode(['resourceLogs' => [$payload]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'timeout' => 1.0,
        ]];
        @file_get_contents($this->endpoint, false, stream_context_create($opts));
    }
}
