<?php

declare(strict_types=1);

namespace App\Applicating\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class SecurityController extends AbstractController
{
    #[Route('/login', name: 'applicating_security_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response|array
    {
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
                'route' => (string) ($request->attributes->get('_route') ?? ''),
            ],
        ];
    }

    #[Route('/logout', name: 'applicating_security_logout')]
    public function logout(): never
    {
        throw new \LogicException('Logout is managed by Symfony security.');
    }
}
