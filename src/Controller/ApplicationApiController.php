<?php

declare(strict_types=1);

namespace App\Applicating\Controller;

use App\Applicating\DTO\ApplicationAdminApiRowDTO;
use App\Applicating\Repository\ApplicationRepository;
use App\Applicating\ServiceInterface\ApplicationReportServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Exposes manager-authorized application inventory and reporting data as JSON.
 */
final class ApplicationApiController extends AbstractController
{
    /**
     * Returns the ordered application inventory projected into the public admin API row contract.
     */
    #[Route('/api/applicating/application', name: 'applicating_application_api_index', methods: ['GET'])]
    public function index(ApplicationRepository $applicationRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_APPLICATION_MANAGER');

        $rows = array_map(
            static fn ($application): array => ApplicationAdminApiRowDTO::fromApplication($application)->toArray(),
            $applicationRepository->findOrderedForAdmin(),
        );

        return $this->json(['applications' => $rows]);
    }

    /**
     * Returns the current aggregate application report for authorized managers.
     */
    #[Route('/api/applicating/application/report', name: 'applicating_application_api_report', methods: ['GET'])]
    public function report(ApplicationReportServiceInterface $applicationReportService): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_APPLICATION_MANAGER');

        return $this->json(['summary' => $applicationReportService->buildSummary()->toArray()]);
    }
}
