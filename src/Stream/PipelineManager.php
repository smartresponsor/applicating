<?php

declare(strict_types=1);

namespace App\Component\Product\Stream;

use App\Component\Product\Stream\DTO\EventMessage;

final class PipelineManager
{
    public function __construct(
        private readonly Producer $producer,
        private readonly Consumer $consumer,
        private readonly ForecastWorker $worker,
    ) {
    }

    public function publishForecast(string $tenantId, string $target, float $value): void
    {
        $this->producer->send('smart.events', new EventMessage(
            type: 'forecast.request',
            tenantId: $tenantId,
            payload: ['target' => $target, 'value' => $value],
            ts: gmdate('c')
        ));
    }

    /** Обработать очередь и записать прогноз */
    public function runOnce(): array
    {
        $batch = $this->consumer->poll('smart.events', 50);
        $out = [];
        foreach ($batch as $msg) {
            $event = [
                'id' => $msg['id'],
                'tenant_id' => $msg['tenant_id'],
                'type' => $msg['type'],
                'payload' => json_decode($msg['payload'] ?? '{}', true) ?: [],
            ];
            $res = $this->worker->handle($event);
            $this->consumer->ack((int) $msg['id']);
            $out[] = ['id' => $msg['id'], 'result' => $res];
        }

        return $out;
    }
}
