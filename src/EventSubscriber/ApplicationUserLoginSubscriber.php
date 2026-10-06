<?php

declare(strict_types=1);

namespace App\Applicating\EventSubscriber;

use App\Applicating\Entity\ApplicationUserEntity;
use App\Applicating\RepositoryInterface\ApplicationUserRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * Persists the successful-login timestamp for Applicating-owned security users.
 */
final readonly class ApplicationUserLoginSubscriber implements EventSubscriberInterface
{
    public function __construct(private ApplicationUserRepositoryInterface $applicationUserRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    /**
     * Records and persists login success only for the component's local user entity.
     */
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof ApplicationUserEntity) {
            return;
        }

        $user->markLogin();
        $this->applicationUserRepository->save($user);
    }
}
