<?php
declare(strict_types=1);

namespace App\Component\Product\Http\Product\Middleware;

use App\Component\Product\Http\Product\Security\JwtHelper;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

final class JwtAuthMiddleware
{
    public function __construct(private readonly JwtHelper $jwt) {}

    public function __invoke(RequestEvent $event): void
    {
        $r = $event->getRequest();
        $hdr = (string)($r->headers->get('Authorization') ?? '');
        if ($hdr === '' || !str_starts_with($hdr, 'Bearer ')) {
            return; // пропускаем — часть маршрутов может быть публичной
        }
        $token = substr($hdr, 7);
        $payload = $this->jwt->decode($token);
        if ($payload === null) {
            $event->setResponse(new JsonResponse(['error' => 'unauthorized'], 401));
            return;
        }
        $r->attributes->set('jwt', $payload);
    }
}
