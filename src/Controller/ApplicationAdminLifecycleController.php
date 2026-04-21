<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Application\Controller;

use App\Application\DTO\Application\ApplicationManifestData;
use App\Application\DTO\Application\ApplicationReleaseData;
use App\Application\DTO\Application\TenantApplicationAssignmentData;
use App\Application\Entity\Application;
use App\Application\Entity\TenantApplication;
use App\Application\Form\Application\ApplicationManifestType;
use App\Application\Form\Application\ApplicationReleaseType;
use App\Application\Form\Application\TenantApplicationAssignmentType;
use App\Application\Security\Voter\ApplicationVoter;
use App\Application\ServiceInterface\ApplicationLifecycleServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/applications')]
final class ApplicationAdminLifecycleController extends AbstractController
{
    #[Route('/{id}/release', name: 'applicating_application_release', methods: ['POST'])]
    public function createRelease(
        Application $application,
        Request $request,
        ApplicationLifecycleServiceInterface $applicationLifecycleService,
    ): RedirectResponse {
        $this->denyAccessUnlessGranted(ApplicationVoter::EDIT, $application);

        return $this->processLifecycleForm(
            $application,
            $request,
            ApplicationReleaseType::class,
            new ApplicationReleaseData(),
            fn (ApplicationReleaseData $data): string => sprintf(
                'Release %s created.',
                $applicationLifecycleService->createRelease($application, $data)->getVersion(),
            ),
            'Release form contains errors.',
        );
    }

    #[Route('/{id}/manifest', name: 'applicating_application_manifest', methods: ['POST'])]
    public function createManifest(
        Application $application,
        Request $request,
        ApplicationLifecycleServiceInterface $applicationLifecycleService,
    ): RedirectResponse {
        $this->denyAccessUnlessGranted(ApplicationVoter::EDIT, $application);

        return $this->processLifecycleForm(
            $application,
            $request,
            ApplicationManifestType::class,
            new ApplicationManifestData(),
            fn (ApplicationManifestData $data): string => sprintf(
                'Manifest %s attached.',
                $applicationLifecycleService->createManifest($application, $data)->getIdentifier(),
            ),
            'Manifest form contains errors.',
        );
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

        return $this->processLifecycleForm(
            $application,
            $request,
            TenantApplicationAssignmentType::class,
            new TenantApplicationAssignmentData(),
            fn (TenantApplicationAssignmentData $data): string => sprintf(
                'Tenant "%s" assigned.',
                $applicationLifecycleService->assignTenant($application, $data)->getTenantKey(),
            ),
            'Tenant assignment form contains errors.',
        );
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

    /**
     * @template T of object
     *
     * @param class-string        $formType
     * @param T                   $data
     * @param callable(T): string $onValid
     */
    private function processLifecycleForm(
        Application $application,
        Request $request,
        string $formType,
        object $data,
        callable $onValid,
        string $errorMessage,
    ): RedirectResponse {
        $form = $this->createForm($formType, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', $onValid($data));
        } else {
            $this->addFlash('danger', $errorMessage);
        }

        return $this->redirectToRoute('applicating_application_show', ['id' => $application->getId()]);
    }
}
