<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ApplicationRepository;
use App\ServiceInterface\ApplicationReportServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/applications')]
final class ApplicationApiController extends AbstractController
{
    #[Route('', name: 'applicating_application_api_index', methods: ['GET'])]
    public function index(ApplicationRepository $applicationRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_APPLICATION_VIEWER');

        $rows = array_map(static fn ($application): array => [
            'id' => $application->getId(),
            'name' => $application->getName(),
            'slug' => $application->getSlug(),
            'packageName' => $application->getPackageName(),
            'developerName' => $application->getDeveloperName(),
            'publicationState' => $application->getPublicationState()->value,
            'accessLevel' => $application->getAccessLevel()->value,
            'releaseCount' => $application->getReleases()->count(),
            'tenantAssignmentCount' => $application->getTenantApplications()->count(),
        ], $applicationRepository->findOrderedForAdmin());

        return $this->json(['applications' => $rows]);
    }

    #[Route('/report', name: 'applicating_application_api_report', methods: ['GET'])]
    public function report(ApplicationReportServiceInterface $applicationReportService): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_APPLICATION_VIEWER');

        return $this->json(['summary' => $applicationReportService->buildSummary()]);
    }
}
