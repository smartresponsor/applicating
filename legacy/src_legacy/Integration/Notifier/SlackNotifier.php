<?php

declare(strict_types=1);

namespace App\Component\Product\Integration\Notifier;

final class SlackNotifier
{
    public function __construct(private readonly string $webhookUrl)
    {
    }

    public function send(string $text, string $emoji = ':rocket:'): bool
    {
        $payload = json_encode(['text' => $emoji.' '.$text], JSON_UNESCAPED_UNICODE);
        $opts = ['http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => $payload,
            'timeout' => 3,
        ]];
        $ctx = stream_context_create($opts);

        return (bool) @file_get_contents($this->webhookUrl, false, $ctx);
    }
}
