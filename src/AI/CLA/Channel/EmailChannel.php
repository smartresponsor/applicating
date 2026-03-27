<?php

declare(strict_types=1);

namespace App\Component\Product\AI\CLA\Channel;

final class EmailChannel
{
    /** @param array<string,mixed> $vars */
    public function send(string $to, string $template, array $vars): array
    {
        // demo: имитация отправки
        return ['ok' => true, 'to' => $to, 'template' => $template, 'vars' => $vars];
    }
}
