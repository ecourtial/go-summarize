<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Page>
 */
class PageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }

    public function findOneByUrl(string $url): ?Page
    {
        return $this->findOneBy(['url' => $url]);
    }

    public function save(Page $page, bool $flush = false): void
    {
        $this->getEntityManager()->persist($page);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function deleteOldPages(): void
    {
        $now = new \DateTimeImmutable();
        $limitDate = $now->modify('-'.Page::PAGE_LIMIT_AGE.'days');

        $this->createQueryBuilder('p')
            ->delete()
            ->where('p.createdAt < :now')
            ->setParameter('now', $limitDate)
            ->getQuery()
            ->execute();
    }
}
