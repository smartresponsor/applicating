<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\ApplicationUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final readonly class ApplicationUserLoginSubscriber implements EventSubscriberInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
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
        if (!$user instanceof ApplicationUser) {
            return;
        }

        $user->markLogin();
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
