<?php
declare(strict_types=1);

namespace App\Component\Product\Http\Product\Middleware;

use Redis;

final class RedisTokenBucketStore implements TokenBucketStoreInterface
{
    public function __construct(private readonly Redis $redis) {}

    public function getTokens(string $key): int
    {
        $v = $this->redis->get($key);
        return $v !== false ? (int)$v : 0;
    }

    public function setTokens(string $key, int $tokens, int $ttlSeconds): void
    {
        $this->redis->setex($key, $ttlSeconds, (string)$tokens);
    }

    public function now(): int { return time(); }
}
