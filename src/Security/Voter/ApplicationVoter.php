<?php

declare(strict_types=1);

namespace App\Applicating\Security\Voter;

use App\Applicating\Entity\Application;
use App\Applicating\Entity\TenantApplication;
use App\Applicating\Enum\ApplicationPublicationState;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Application|TenantApplication>
 */
final class ApplicationVoter extends Voter
{
    public const string EDIT = 'APPLICATION_EDIT';
    public const string PUBLISH = 'APPLICATION_PUBLISH';
    public const string ASSIGN = 'APPLICATION_ASSIGN';
    public const string TOGGLE = 'APPLICATION_TOGGLE';

    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::PUBLISH, self::ASSIGN, self::TOGGLE], true)
            && ($subject instanceof Application || $subject instanceof TenantApplication);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if ($this->security->isGranted('ROLE_APPLICATION_ADMIN')) {
            return true;
        }

        if ($subject instanceof Application) {
            return match ($attribute) {
                self::EDIT => $this->security->isGranted('ROLE_APPLICATION_MANAGER'),
                self::PUBLISH => $this->security->isGranted('ROLE_APPLICATION_MANAGER')
                    && ApplicationPublicationState::Suspended !== $subject->getPublicationState(),
                self::ASSIGN => $this->security->isGranted('ROLE_APPLICATION_MANAGER')
                    && ApplicationPublicationState::Published === $subject->getPublicationState(),
                default => false,
            };
        }

        return self::TOGGLE === $attribute
            && $this->security->isGranted('ROLE_APPLICATION_MANAGER');
    }
}
