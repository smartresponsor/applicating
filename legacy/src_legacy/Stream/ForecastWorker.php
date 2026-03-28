<?php

declare(strict_types=1);

namespace App\Component\Product\Stream;

final class ForecastWorker
{
    public function __construct(private readonly \Doctrine\DBAL\Connection $db)
    {
    }

    /** @param array<string,mixed> $event */
    public function handle(array $event): array
    {
        // Упрощённая логика «прогноза» для демо
        $target = $event['payload']['target'] ?? 'revenue';
        $base = (float) ($event['payload']['value'] ?? 100.0);
        $pred = $base * 1.05; // +5%
        $err = null;

        $this->db->insert('forecast_results', [
            'ts' => gmdate('c'),
            'tenant_id' => $event['tenant_id'] ?? 'tenantA',
            'model' => 'demo_ar',
            'target' => $target,
            'predicted' => $pred,
            'actual' => null,
            'error' => null,
            'feedback_score' => null,
        ]);

        return ['target' => $target, 'predicted' => $pred, 'error' => $err];
    }
}
