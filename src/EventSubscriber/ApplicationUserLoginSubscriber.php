<?php

declare(strict_types=1);

namespace App\Applicating\EventSubscriber;

use App\Applicating\Entity\ApplicationUserEntity;
use App\Applicating\Repository\ApplicationUserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final readonly class ApplicationUserLoginSubscriber implements EventSubscriberInterface
{
    public function __construct(private ApplicationUserRepository $applicationUserRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

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
