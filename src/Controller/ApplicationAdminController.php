<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Application\ApplicationManifestData;
use App\DTO\Application\ApplicationPublishEligibility;
use App\DTO\Application\ApplicationReleaseData;
use App\DTO\Application\ApplicationUpsertData;
use App\DTO\Application\TenantApplicationAssignmentData;
use App\Entity\Application;
use App\Entity\TenantApplication;
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
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    #[Route('/{id}/release', name: 'applicating_application_release', methods: ['POST'])]
    public function createRelease(
        Application $application,
        Request $request,
        ApplicationLifecycleServiceInterface $applicationLifecycleService,
    ): RedirectResponse {
        $this->denyAccessUnlessGranted(ApplicationVoter::EDIT, $application);

        $data = new ApplicationReleaseData();
        $form = $this->createForm(ApplicationReleaseType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $release = $applicationLifecycleService->createRelease($application, $data);
            $this->addFlash('success', sprintf('Release %s created.', $release->getVersion()));
        } else {
            $this->addFlash('danger', 'Release form contains errors.');
        }

        return $this->redirectToRoute('applicating_application_show', ['id' => $application->getId()]);
    }

    #[Route('/{id}/manifest', name: 'applicating_application_manifest', methods: ['POST'])]
    public function createManifest(
        Application $application,
        Request $request,
        ApplicationLifecycleServiceInterface $applicationLifecycleService,
    ): RedirectResponse {
        $this->denyAccessUnlessGranted(ApplicationVoter::EDIT, $application);

        $data = new ApplicationManifestData();
        $form = $this->createForm(ApplicationManifestType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manifest = $applicationLifecycleService->createManifest($application, $data);
            $this->addFlash('success', sprintf('Manifest %s attached.', $manifest->getIdentifier()));
        } else {
            $this->addFlash('danger', 'Manifest form contains errors.');
        }

        return $this->redirectToRoute('applicating_application_show', ['id' => $application->getId()]);
    }

    #[Route('/{id}/publish/{releaseId}', name: 'applicating_application_publish', methods: ['POST'])]
    public function publish(
        Application $application,
        int $releaseId,
        ApplicationLifecycleServiceInterface $applicationLifecycleService,
    ): RedirectResponse {
        $this->denyAccessUnlessGranted(ApplicationVoter::PUBLISH, $application);

        foreach ($application->getReleases() as $release) {
            if ($release->getId() === $releaseId) {
                try {
                    $applicationLifecycleService->publishApplication($application, $release);
                    $this->addFlash('success', sprintf('Application "%s" published with release %s.', $application->getName(), $release->getVersion()));
                } catch (\LogicException $exception) {
                    $this->addFlash('danger', $exception->getMessage());
                }

                return $this->redirectToRoute('applicating_application_show', ['id' => $application->getId()]);
            }
        }

        throw $this->createNotFoundException('Release not found for application.');
    }

    #[Route('/{id}/suspend', name: 'applicating_application_suspend', methods: ['POST'])]
    public function suspend(
        Application $application,
        ApplicationLifecycleServiceInterface $applicationLifecycleService,
    ): RedirectResponse {
        $this->denyAccessUnlessGranted(ApplicationVoter::PUBLISH, $application);

        $applicationLifecycleService->suspendApplication($application);
        $this->addFlash('warning', sprintf('Application "%s" suspended.', $application->getName()));

        return $this->redirectToRoute('applicating_application_show', ['id' => $application->getId()]);
    }

    #[Route('/{id}/assign', name: 'applicating_application_assign', methods: ['POST'])]
    public function assign(
        Application $application,
        Request $request,
        ApplicationLifecycleServiceInterface $applicationLifecycleService,
    ): RedirectResponse {
        $this->denyAccessUnlessGranted(ApplicationVoter::ASSIGN, $application);

        $data = new TenantApplicationAssignmentData();
        $form = $this->createForm(TenantApplicationAssignmentType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $assignment = $applicationLifecycleService->assignTenant($application, $data);
            $this->addFlash('success', sprintf('Tenant "%s" assigned.', $assignment->getTenantKey()));
        } else {
            $this->addFlash('danger', 'Tenant assignment form contains errors.');
        }

        return $this->redirectToRoute('applicating_application_show', ['id' => $application->getId()]);
    }

    #[Route('/tenant-assignment/{id}/toggle', name: 'applicating_tenant_application_toggle', methods: ['POST'])]
    public function toggle(
        TenantApplication $tenantApplication,
        Request $request,
        ApplicationLifecycleServiceInterface $applicationLifecycleService,
    ): RedirectResponse {
        $this->denyAccessUnlessGranted(ApplicationVoter::TOGGLE, $tenantApplication);

        $enabled = '1' === (string) $request->request->get('enabled');
        $applicationLifecycleService->toggleTenantApplication($tenantApplication, $enabled);
        $this->addFlash('success', sprintf(
            'Tenant assignment for "%s" is now %s.',
            $tenantApplication->getTenantKey(),
            $enabled ? 'enabled' : 'disabled',
        ));

        return $this->redirectToRoute('applicating_application_show', ['id' => $tenantApplication->getApplication()->getId()]);
    }
}
