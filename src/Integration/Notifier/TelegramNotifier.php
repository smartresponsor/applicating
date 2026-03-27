<?php

declare(strict_types=1);

namespace App\Component\Product\Integration\Notifier;

final class TelegramNotifier
{
    public function __construct(
        private readonly string $botToken,
        private readonly string $chatId,
    ) {
    }

    public function send(string $text): bool
    {
        $url = sprintf(
            'https://api.telegram.org/bot%s/sendMessage?chat_id=%s&text=%s',
            $this->botToken,
            $this->chatId,
            urlencode($text)
        );

        return (bool) @file_get_contents($url);
    }
}
