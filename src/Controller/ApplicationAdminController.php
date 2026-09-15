<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Applicating\Controller;

use App\Applicating\DTO\ApplicationManifestDTO;
use App\Applicating\DTO\ApplicationPublishEligibilityDTO;
use App\Applicating\DTO\ApplicationReleaseDTO;
use App\Applicating\DTO\ApplicationUpsertDTO;
use App\Applicating\DTO\TenantApplicationAssignmentDTO;
use App\Applicating\Entity\Application;
use App\Applicating\Form\ApplicationManifestType;
use App\Applicating\Form\ApplicationReleaseType;
use App\Applicating\Form\ApplicationType;
use App\Applicating\Form\TenantApplicationAssignmentType;
use App\Applicating\Repository\ApplicationRepository;
use App\Applicating\Security\Voter\ApplicationVoter;
use App\Applicating\ServiceInterface\ApplicationAdminViewBuilderInterface;
use App\Applicating\ServiceInterface\ApplicationLifecycleServiceInterface;
use App\Applicating\ServiceInterface\ApplicationPublishEligibilityServiceInterface;
use App\Applicating\ServiceInterface\ApplicationReportServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/applications')]
final class ApplicationAdminController extends AbstractController
{
    /** @return array<string, mixed> */
    #[Route('', name: 'applicating_application_index', methods: ['GET'])]
    public function index(
        ApplicationRepository $applicationRepository,
        ApplicationReportServiceInterface $applicationReportService,
        ApplicationAdminViewBuilderInterface $applicationAdminViewBuilder,
    ): array {
        $this->denyAccessUnlessGranted('ROLE_APPLICATION_VIEWER');

        return $this->viewPayload('index', [
            'applicationRows' => $applicationAdminViewBuilder->buildIndexRows($applicationRepository->findOrderedForAdmin()),
            'summary' => $applicationReportService->buildSummary(),
        ], 'Application administration index');
    }

    /** @return Response|array<string, mixed> */
    #[Route('/new', name: 'applicating_application_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ApplicationLifecycleServiceInterface $applicationLifecycleService): Response|array
    {
        $this->denyAccessUnlessGranted('ROLE_APPLICATION_MANAGER');

        $data = new ApplicationUpsertDTO();
        $form = $this->createForm(ApplicationType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $application = $applicationLifecycleService->createApplication($data);
            $this->addFlash('success', sprintf('Application "%s" created.', $application->getName()));

            return $this->redirectToRoute('applicating_application_show', ['id' => $application->getId()]);
        }

        return $this->viewPayload('new', [
            'form' => $form->createView(),
        ], 'Create application');
    }

    /** @return array<string, mixed> */
    #[Route('/report', name: 'applicating_application_report', methods: ['GET'])]
    public function report(ApplicationReportServiceInterface $applicationReportService): array
    {
        $this->denyAccessUnlessGranted('ROLE_APPLICATION_VIEWER');

        return $this->viewPayload('report', [
            'summary' => $applicationReportService->buildSummary(),
        ], 'Application report');
    }

    /** @return array<string, mixed> */
    #[Route('/{id}', name: 'applicating_application_show', methods: ['GET', 'POST'])]
    public function show(
        Application $application,
        ApplicationPublishEligibilityServiceInterface $applicationPublishEligibilityService,
        ApplicationAdminViewBuilderInterface $applicationAdminViewBuilder,
    ): array {
        $this->denyAccessUnlessGranted('ROLE_APPLICATION_VIEWER');

        $releaseForm = $this->createForm(ApplicationReleaseType::class, new ApplicationReleaseDTO(), [
            'action' => $this->generateUrl('applicating_application_release', ['id' => $application->getId()]),
        ]);
        $manifestForm = $this->createForm(ApplicationManifestType::class, new ApplicationManifestDTO(), [
            'action' => $this->generateUrl('applicating_application_manifest', ['id' => $application->getId()]),
        ]);
        $assignmentForm = $this->createForm(TenantApplicationAssignmentType::class, new TenantApplicationAssignmentDTO(), [
            'action' => $this->generateUrl('applicating_application_assign', ['id' => $application->getId()]),
        ]);

        $applicationView = $applicationAdminViewBuilder->buildShowView($application);
        $togglePermissions = [];
        foreach ($application->getTenantApplications() as $tenantApplication) {
            $tenantApplicationId = $tenantApplication->getId();
            if (null !== $tenantApplicationId) {
                $togglePermissions[$tenantApplicationId] = $this->isGranted(ApplicationVoter::TOGGLE, $tenantApplication);
            }
        }

        return $this->viewPayload('show', [
            'application' => $application,
            'applicationView' => $applicationView,
            'releaseForm' => $releaseForm->createView(),
            'manifestForm' => $manifestForm->createView(),
            'assignmentForm' => $assignmentForm->createView(),
            'publishEligibility' => array_reduce(
                $applicationPublishEligibilityService->buildEligibilityMap($application),
                static function (array $carry, ApplicationPublishEligibilityDTO $eligibility): array {
                    $carry[$eligibility->releaseId] = $eligibility->toLegacyMapItem();

                    return $carry;
                },
                [],
            ),
            'canEdit' => $this->isGranted(ApplicationVoter::EDIT, $application),
            'canPublish' => $this->isGranted(ApplicationVoter::PUBLISH, $application),
            'canAssign' => $this->isGranted(ApplicationVoter::ASSIGN, $application),
            'togglePermissions' => $togglePermissions,
        ], 'Application detail');
    }

    /** @return Response|array<string, mixed> */
    #[Route('/{id}/edit', name: 'applicating_application_edit', methods: ['GET', 'POST'])]
    public function edit(
        Application $application,
        Request $request,
        ApplicationLifecycleServiceInterface $applicationLifecycleService,
    ): Response|array {
        $this->denyAccessUnlessGranted(ApplicationVoter::EDIT, $application);

        $data = new ApplicationUpsertDTO();
        $data->nameEntity = $application->getName();
        $data->slug = $application->getSlug();
        $data->packageName = $application->getPackageName();
        $data->developerName = $application->getDeveloperName();
        $data->listingSummary = $application->getListingSummary();
        $data->accessLevel = $application->getAccessLevel()->value;
        $data->billingCode = $application->getBillingCode();
        $data->sandboxProfile = $application->getSandboxProfile();
        $data->enabledByDefault = $application->isEnabledByDefault();

        $form = $this->createForm(ApplicationType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $applicationLifecycleService->updateApplication($application, $data);
            $this->addFlash('success', sprintf('Application "%s" updated.', $application->getName()));

            return $this->redirectToRoute('applicating_application_show', ['id' => $application->getId()]);
        }

        return $this->viewPayload('edit', [
            'application' => $application,
            'form' => $form->createView(),
        ], 'Edit application');
    }

    /**
     * Build a neutral Viewing payload for application administration pages.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function viewPayload(string $operation, array $data, string $title): array
    {
        return [
            '_view' => [
                'surface' => 'application',
                'operation' => $operation,
                'component' => 'Applicating',
                'intent' => 'admin',
            ],
            'locations' => [
                'body' => [
                    'title' => $title,
                    'operation' => $operation,
                    'managed_by' => 'Viewing',
                ],
            ],
            'data' => $data,
            'meta' => [
                'source_controller' => self::class,
                'template_family' => 'application',
            ],
        ];
    }
}
