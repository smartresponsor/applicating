<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Application\ApplicationManifestData;
use App\DTO\Application\ApplicationPublishEligibility;
use App\DTO\Application\ApplicationReleaseData;
use App\DTO\Application\ApplicationUpsertData;
use App\DTO\Application\TenantApplicationAssignmentData;
use App\Entity\Application;
use App\Form\Application\ApplicationManifestType;
use App\Form\Application\ApplicationReleaseType;
use App\Form\Application\ApplicationType;
use App\Form\Application\TenantApplicationAssignmentType;
use App\Repository\ApplicationRepository;
use App\Security\Voter\ApplicationVoter;
use App\ServiceInterface\ApplicationAdminViewBuilderInterface;
use App\ServiceInterface\ApplicationLifecycleServiceInterface;
use App\ServiceInterface\ApplicationPublishEligibilityServiceInterface;
use App\ServiceInterface\ApplicationReportServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/applications')]
final class ApplicationAdminController extends AbstractController
{
    #[Route('', name: 'applicating_application_index', methods: ['GET'])]
    public function index(
        ApplicationRepository $applicationRepository,
        ApplicationReportServiceInterface $applicationReportService,
        ApplicationAdminViewBuilderInterface $applicationAdminViewBuilder,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_APPLICATION_VIEWER');

        return $this->render('application/index.html.twig', [
            'applicationRows' => $applicationAdminViewBuilder->buildIndexRows($applicationRepository->findOrderedForAdmin()),
            'summary' => $applicationReportService->buildSummary(),
        ]);
    }

    #[Route('/new', name: 'applicating_application_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ApplicationLifecycleServiceInterface $applicationLifecycleService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_APPLICATION_MANAGER');

        $data = new ApplicationUpsertData();
        $form = $this->createForm(ApplicationType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $application = $applicationLifecycleService->createApplication($data);
            $this->addFlash('success', sprintf('Application "%s" created.', $application->getName()));

            return $this->redirectToRoute('applicating_application_show', ['id' => $application->getId()]);
        }

        return $this->render('application/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/report', name: 'applicating_application_report', methods: ['GET'])]
    public function report(ApplicationReportServiceInterface $applicationReportService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_APPLICATION_VIEWER');

        return $this->render('application/report.html.twig', [
            'summary' => $applicationReportService->buildSummary(),
        ]);
    }

    #[Route('/{id}', name: 'applicating_application_show', methods: ['GET', 'POST'])]
    public function show(
        Application $application,
        ApplicationPublishEligibilityServiceInterface $applicationPublishEligibilityService,
        ApplicationAdminViewBuilderInterface $applicationAdminViewBuilder,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_APPLICATION_VIEWER');

        $releaseForm = $this->createForm(ApplicationReleaseType::class, new ApplicationReleaseData(), [
            'action' => $this->generateUrl('applicating_application_release', ['id' => $application->getId()]),
        ]);
        $manifestForm = $this->createForm(ApplicationManifestType::class, new ApplicationManifestData(), [
            'action' => $this->generateUrl('applicating_application_manifest', ['id' => $application->getId()]),
        ]);
        $assignmentForm = $this->createForm(TenantApplicationAssignmentType::class, new TenantApplicationAssignmentData(), [
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

        return $this->render('application/show.html.twig', [
            'application' => $application,
            'applicationView' => $applicationView,
            'releaseForm' => $releaseForm->createView(),
            'manifestForm' => $manifestForm->createView(),
            'assignmentForm' => $assignmentForm->createView(),
            'publishEligibility' => array_reduce(
                $applicationPublishEligibilityService->buildEligibilityMap($application),
                static function (array $carry, ApplicationPublishEligibility $eligibility): array {
                    $carry[$eligibility->releaseId] = $eligibility->toLegacyMapItem();

                    return $carry;
                },
                [],
            ),
            'canEdit' => $this->isGranted(ApplicationVoter::EDIT, $application),
            'canPublish' => $this->isGranted(ApplicationVoter::PUBLISH, $application),
            'canAssign' => $this->isGranted(ApplicationVoter::ASSIGN, $application),
            'togglePermissions' => $togglePermissions,
        ]);
    }

    #[Route('/{id}/edit', name: 'applicating_application_edit', methods: ['GET', 'POST'])]
    public function edit(
        Application $application,
        Request $request,
        ApplicationLifecycleServiceInterface $applicationLifecycleService,
    ): Response {
        $this->denyAccessUnlessGranted(ApplicationVoter::EDIT, $application);

        $data = new ApplicationUpsertData();
        $data->name = $application->getName();
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

        return $this->render('application/edit.html.twig', [
            'application' => $application,
            'form' => $form->createView(),
        ]);
    }
}
