<?php
declare(strict_types=1);

namespace App\Component\Product\Http\Product\Middleware;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

interface TokenBucketStoreInterface
{
    public function getTokens(string $key): int;
    public function setTokens(string $key, int $tokens, int $ttlSeconds): void;
    public function now(): int;
}

final class InMemoryTokenBucketStore implements TokenBucketStoreInterface
{
    private array $data = [];
    public function getTokens(string $key): int { return $this->data[$key]['tokens'] ?? 0; }
    public function setTokens(string $key, int $tokens, int $ttlSeconds): void { $this->data[$key] = ['tokens' => $tokens, 'exp' => time()+$ttlSeconds]; }
    public function now(): int { return time(); }
}

final class RateLimitMiddleware
{
    public function __construct(
        private readonly TokenBucketStoreInterface $store,
        private readonly int $capacity = 60,
        private readonly int $refillPerMinute = 60
    ) {}

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $key = $this->key($request);
        $now = $this->store->now();
        $ttl = 60;

        $tokens = $this->store->getTokens($key);
        if ($tokens <= 0) {
            $tokens = $this->capacity;
        }
        // refill
        $tokens = min($this->capacity, $tokens + (int)($this->refillPerMinute / 60));

        if ($tokens <= 0) {
            $event->setResponse(new JsonResponse({'error': 'rate_limited'}, 429));
            return;
        }
        $tokens -= 1;
        $this->store->setTokens($key, $tokens, $ttl);
    }

    private function key(Request $r): string
    {
        $ip = $r->getClientIp() ?? '0.0.0.0';
        return 'rl:' . $ip . ':' . $r->getPathInfo();
    }
}
