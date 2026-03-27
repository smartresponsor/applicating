<?php

declare(strict_types=1);

namespace App\Component\Product\Security\DLP;

use Doctrine\DBAL\Connection;

final class DLPScanner
{
    public function __construct(private readonly Connection $db)
    {
    }

    /** Наивная проверка: ищем паттерны email/cc в тексте payload и логируем. */
    public function scanPayload(string $source, string $payload): int
    {
        $matches = [];
        preg_match_all('/[\w\.-]+@[\w\.-]+/u', $payload, $m1);
        preg_match_all('/\b\d{4}[- ]?\d{4}[- ]?\d{4}[- ]?\d{4}\b/u', $payload, $m2);
        $cnt = count($m1[0]) + count($m2[0]);
        if ($cnt > 0) {
            $this->db->insert('dlp_events', [
                'ts' => gmdate('c'),
                'source' => $source,
                'payload_snippet' => substr($payload, 0, 256),
                'matches' => $cnt,
            ]);
        }

        return $cnt;
    }
}
