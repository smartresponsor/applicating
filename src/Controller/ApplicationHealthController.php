<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Controller;

use App\ServiceInterface\ApplicationHealthServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ApplicationHealthController extends AbstractController
{
    public function __construct(private readonly ApplicationHealthServiceInterface $applicationHealthService)
    {
    }

    #[Route('/health', name: 'applicating_health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        return $this->json($this->applicationHealthService->buildHealth());
    }

    #[Route('/ready', name: 'applicating_ready', methods: ['GET'])]
    public function ready(): JsonResponse
    {
        $payload = $this->applicationHealthService->buildReadiness();
        $statusCode = 'ready' === $payload['status'] ? 200 : 503;

        return $this->json($payload, $statusCode);
    }
}
