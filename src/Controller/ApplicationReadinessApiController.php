<?php

declare(strict_types=1);

namespace App\Controller;

use App\ServiceInterface\ApplicationReadinessServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ApplicationReadinessApiController extends AbstractController
{
    public function __construct(
        private readonly ApplicationReadinessServiceInterface $applicationReadinessService,
    ) {
    }

    #[Route('/api/admin/applications/{slug}/readiness', name: 'api_application_readiness', methods: ['GET'])]
    public function __invoke(string $slug): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN_VIEWER');

        $readiness = $this->applicationReadinessService->buildReadiness($slug);

        return $this->json([
            'canPublish' => $readiness->canPublish,
            'blockingReasons' => $readiness->blockingReasons,
            'warnings' => $readiness->warnings,
            'signals' => $readiness->signals,
        ]);
    }
}
