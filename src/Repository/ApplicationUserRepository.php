<?php

declare(strict_types=1);

namespace App\Application\Repository;

use App\Application\Entity\ApplicationUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<ApplicationUser>
 */
final class ApplicationUserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApplicationUser::class);
    }

    public function findOneByIdentifier(string $userIdentifier): ?ApplicationUser
    {
        /** @var ApplicationUser|null $user */
        $user = $this->findOneBy(['userIdentifier' => $userIdentifier]);

        return $user;
    }

    public function countActiveUsers(): int
    {
        return (int) $this->createQueryBuilder('applicationUser')
            ->select('COUNT(applicationUser.id)')
            ->where('applicationUser.active = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof ApplicationUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->changePassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
}
