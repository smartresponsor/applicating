<?php

declare(strict_types=1);

namespace App\Component\Product\Http\Product\Middleware;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

final class SecurityHeadersMiddleware
{
    public function __invoke(ResponseEvent $event): void
    {
        $r = $event->getResponse();
        // Minimal sane defaults (tune for your app / CDN):
        $r->headers->set('X-Content-Type-Options', 'nosniff');
        $r->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $r->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');
        $r->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        $r->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        // Basic CSP (adapt static assets / hashes as needed):
        $r->headers->set('Content-Security-Policy', "default-src 'self'; img-src 'self' data:; object-src 'none'; frame-ancestors 'self'; base-uri 'self'");
    }
}
