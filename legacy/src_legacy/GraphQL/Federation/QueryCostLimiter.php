<?php

declare(strict_types=1);

namespace App\Component\Product\GraphQL\Federation;

final class QueryCostLimiter
{
    /** Наивная оценка стоимости: количество полей в запросе. */
    public function cost(string $query): int
    {
        $q = preg_replace('/\s+/', ' ', $query);
        preg_match_all('/\b[a-zA-Z_][a-zA-Z0-9_]*\s*\(/', $q ?? '', $fn);
        preg_match_all('/\b[a-zA-Z_][a-zA-Z0-9_]*\b/', $q ?? '', $words);

        return min(10000, max(count($words[0]) // общее число токенов
            , count($fn[0]) * 5));
    }

    public function enforce(int $cost, int $limit = 1500): void
    {
        if ($cost > $limit) {
            throw new \RuntimeException('GraphQL query too expensive');
        }
    }
}
