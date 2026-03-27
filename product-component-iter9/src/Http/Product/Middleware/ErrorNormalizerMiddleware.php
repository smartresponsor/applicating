<?php
declare(strict_types=1);

namespace App\Component\Product\Http\Product\Middleware;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

final class ErrorNormalizerMiddleware
{
    public function __invoke(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();
        $status = $this->mapStatus($e);
        $payload = [
            'error' => [
                'type' => (new \ReflectionClass($e))->getShortName(),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]
        ];
        $event->setResponse(new JsonResponse($payload, $status));
    }

    private function mapStatus(\Throwable $e): int
    {
        return $e instanceof \InvalidArgumentException ? 400
            : ($e instanceof \RuntimeException ? 404 : 500);
    }
}
