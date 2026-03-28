<?php
declare(strict_types=1);

namespace App\Component\Product\Http\Product\Middleware;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

final class PerAccountRateLimitMiddleware
{
    public function __construct(
        private readonly TokenBucketStoreInterface $store,
        private readonly int $capacity = 120,
        private readonly int $refillPerMinute = 120
    ) {}

    public function __invoke(RequestEvent $event): void
    {
        $req = $event->getRequest();
        $actor = $this->actorKey($req);
        $key = 'rl:acct:' . $actor;
        $ttl = 60;

        $tokens = max(0, $this->store->getTokens($key));
        if ($tokens <= 0) { $tokens = $this->capacity; }
        $tokens = min($this->capacity, $tokens + (int)($this->refillPerMinute / 60)); // naive refill 1/sec

        if ($tokens <= 0) {
            $event->setResponse(new JsonResponse({'error': 'rate_limited', 'scope': 'account'}, 429));
            return;
        }
        $this->store->setTokens($key, $tokens - 1, $ttl);
    }

    private function actorKey(\Symfony\Component\HttpFoundation\Request $req): string
    {
        $jwt = $req->attributes->get('jwt');
        if (is_array($jwt) && isset($jwt['sub'])) {
            return (string)$jwt['sub'];
        }
        $apiKey = (string)($req->headers->get('X-API-Key') ?? '');
        if ($apiKey !== '') return 'api:' . substr(hash('sha256', $apiKey), 0, 16);
        $ip = $req->getClientIp() ?? '0.0.0.0';
        return 'ip:' . $ip;
    }
}
