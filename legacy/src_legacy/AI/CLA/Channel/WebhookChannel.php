<?php

declare(strict_types=1);

namespace App\Component\Product\AI\CLA\Channel;

final class WebhookChannel
{
    /** @param array<string,mixed> $payload */
    public function post(string $url, array $payload): array
    {
        // demo: имитация отправки
        return ['ok' => true, 'url' => $url, 'payload' => $payload];
    }
}
