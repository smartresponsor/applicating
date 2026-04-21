<?php

declare(strict_types=1);

namespace App\Application\Controller;

use App\Application\DTO\Application\ApplicationAdminApiRow;
use App\Application\Repository\ApplicationRepository;
use App\Application\ServiceInterface\ApplicationReportServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ApplicationApiController extends AbstractController
{
    #[Route('/api/admin/applications', name: 'applicating_application_api_index', methods: ['GET'])]
    #[Route('/api/admin/v1/applications', name: 'applicating_application_api_index_v1', methods: ['GET'])]
    public function index(ApplicationRepository $applicationRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_APPLICATION_MANAGER');

        $rows = array_map(
            static fn ($application): array => ApplicationAdminApiRow::fromApplication($application)->toArray(),
            $applicationRepository->findOrderedForAdmin(),
        );

        return $this->json(['applications' => $rows]);
    }

    #[Route('/api/admin/applications/report', name: 'applicating_application_api_report', methods: ['GET'])]
    #[Route('/api/admin/v1/applications/report', name: 'applicating_application_api_report_v1', methods: ['GET'])]
    public function report(ApplicationReportServiceInterface $applicationReportService): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_APPLICATION_MANAGER');

        return $this->json(['summary' => $applicationReportService->buildSummary()->toArray()]);
    }
}
