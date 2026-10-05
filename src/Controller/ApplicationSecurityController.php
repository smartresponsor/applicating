<?php

declare(strict_types=1);

namespace App\Applicating\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Builds the login presentation payload while delegating logout execution to Symfony security.
 */
final class ApplicationSecurityController extends AbstractController
{
    /**
     * Returns the neutral login view payload with the last identifier and authentication error state.
     *
     * @return array<string, mixed>
     */
    #[Route('/login', name: 'applicating_security_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): array
    {
        $route = $request->attributes->get('_route');
        $payload = [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'title' => 'Sign in',
        ];

        return [
            '_view' => [
                'surface' => 'security',
                'operation' => 'login',
                'component' => 'Applicating',
            ],
            'locations' => [
                'body' => $payload,
            ],
            'data' => $payload,
            'meta' => [
                'source' => 'applicating_security_login',
                'route' => is_string($route) ? $route : '',
            ],
        ];
    }

    /**
     * Marks the Symfony firewall logout endpoint and rejects direct controller execution.
     */
    #[Route('/logout', name: 'applicating_security_logout')]
    public function logout(): never
    {
        throw new \LogicException('Logout is managed by Symfony security.');
    }
}
