<?php
declare(strict_types=1);

namespace App\Component\Product\Metrics;

use Symfony\Component\HttpFoundation\Response;

final class PrometheusExporter
{
    /** @var array<string,int> */
    private array $counters = [];

    public function inc(string $name, int $by = 1): void
    {
        $this->counters[$name] = ($this->counters[$name] ?? 0) + $by;
    }

    public function response(): Response
    {
        $lines = [];
        foreach ($this->counters as $k => $v) {
            $lines[] = sprintf('# TYPE %s counter', $k);
            $lines[] = sprintf('%s %d', $k, $v);
        }
        return new Response(implode("\n", $lines) . "\n", 200, ['Content-Type' => 'text/plain; version=0.0.4']);
    }
}
