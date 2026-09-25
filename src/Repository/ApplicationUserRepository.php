<?php

declare(strict_types=1);

namespace App\Applicating\Repository;

use App\Applicating\Entity\ApplicationUserEntity;
use App\Applicating\RepositoryInterface\ApplicationUserRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<ApplicationUserEntity>
 */
final class ApplicationUserRepository extends ServiceEntityRepository implements ApplicationUserRepositoryInterface, PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApplicationUserEntity::class);
    }

    public function findOneByIdentifier(string $userIdentifier): ?ApplicationUserEntity
    {
        /** @var ApplicationUserEntity|null $user */
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

    public function save(ApplicationUserEntity $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof ApplicationUserEntity) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->changePassword($newHashedPassword);
        $this->save($user);
    }
}
