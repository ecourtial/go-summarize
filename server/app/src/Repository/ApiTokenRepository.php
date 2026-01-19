<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ApiToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApiToken>
 */
class ApiTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiToken::class);
    }

    public function deleteOutdatedApiTokens(): void
    {
        $now = new \DateTimeImmutable();
        $limitDate = $now->modify('-'.ApiToken::TOKEN_MAX_VALIDITY_DAYS.'days');

        $this->createQueryBuilder('t')
            ->delete()
            ->where('t.createdAt < :now')
            ->setParameter('now', $limitDate)
            ->getQuery()
            ->execute();
    }

    public function save(ApiToken $apiToken, bool $flush = false): void
    {
        $this->getEntityManager()->persist($apiToken);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }
}
