<?php

declare(strict_types=1);

namespace App\Component\Product\AbuseGuard\Controller;

use App\Component\Product\AbuseGuard\CaptchaService;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class GuardAPI
{
    public function __construct(private readonly Connection $db, private readonly CaptchaService $captcha)
    {
    }

    #[Route('/api/guard/captcha/verify', methods: ['POST'])]
    public function captcha(Request $req): JsonResponse
    {
        $ip = $req->getClientIp() ?: '0.0.0.0';
        $tenant = (string) ($req->get('tenant') ?? 'tenantA');
        $token = (string) ($req->get('token') ?? '');

        $ok = $this->captcha->verify($token);
        if ($ok) {
            $this->db->executeStatement("DELETE FROM abuse_bans WHERE kind='ip' AND value=?", [$ip]);

            return new JsonResponse(['ok' => true]);
        }

        return new JsonResponse(['ok' => false, 'error' => 'captcha_failed'], 400);
    }
}
