<?php

declare(strict_types=1);

namespace App\Applicating\Controller;

use App\Applicating\ServiceInterface\ApplicationReadinessServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ApplicationReadinessApiController extends AbstractController
{
    public function __construct(
        private readonly ApplicationReadinessServiceInterface $applicationReadinessService,
    ) {
    }

    #[Route('/api/admin/applications/{slug}/readiness', name: 'applicating_application_readiness_api', methods: ['GET'])]
    #[Route('/api/admin/v1/applications/{slug}/readiness', name: 'applicating_application_readiness_api_v1', methods: ['GET'])]
    public function __invoke(string $slug): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_APPLICATION_MANAGER');

        $readiness = $this->applicationReadinessService->buildReadiness($slug);

        return $this->json($readiness->toArray());
    }
}
