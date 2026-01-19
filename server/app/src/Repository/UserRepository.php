<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ApiToken;
use App\Entity\User;
use App\Security\TokenService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry, private readonly TokenService $tokenService)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findOneByValidToken(string $plainAccessToken): ?User
    {
        $tokenHash = $this->tokenService->hash($plainAccessToken);

        $now = new \DateTimeImmutable();
        $limitDate = $now->modify('-'.ApiToken::TOKEN_MAX_VALIDITY_DAYS.'days');

        $qb = $this->getEntityManager()->createQueryBuilder();

        $result = $qb->select('u')
            ->from(User::class, 'u')
            ->join('u.apiTokens', 't')
            ->where('t.tokenHash = :tokenHash')
            ->andWhere('t.createdAt >= :limitDate')
            ->setParameter('tokenHash', $tokenHash)
            ->setParameter('limitDate', $limitDate)
            ->getQuery()
            ->getOneOrNullResult();

        if (null !== $result && false === $result instanceof User) {
            throw new \LogicException('Unexpected hydration result');
        }

        return $result;
    }

    public function save(User $user, bool $flush = false): void
    {
        $this->getEntityManager()->persist($user);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }
}
