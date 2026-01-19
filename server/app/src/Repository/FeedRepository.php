<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Feed;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Feed>
 */
class FeedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Feed::class);
    }

    public function save(Feed $page, bool $flush = false): void
    {
        $this->getEntityManager()->persist($page);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return iterable<Feed>
     */
    public function findFeedsToFetchForNews(\DateTime $today, ?bool $ignoreAlreadyFetchedFeeds = true, ?int $feedId = null): iterable
    {
        $today->setTime(0, 0, 0);

        $qb = $this->createQueryBuilder('f');

        if (null !== $feedId) {
            $qb->andWhere('f.id = :feedId')->setParameter('feedId', $feedId);
        }

        /*
         * Standard behavior, we don't want to waste time to fetch feeds without new content.
         * Otherwise, we will parse all the feeds.
         */
        if (true === $ignoreAlreadyFetchedFeeds) {
            $qb->andWhere('f.lastFetchedAt < :todayStart OR f.lastFetchedAt IS NULL')
                ->setParameter('todayStart', $today)
                ->orderBy('f.lastFetchedAt', 'ASC');
        }

        /*
         * @TODO refacto iterable is not a true iterator.
         * Everything is loaded in memory. We need to loop
         * over small queries and clear EM often.
         */
        foreach ($qb->getQuery()->toIterable() as $feed) {
            if (!$feed instanceof Feed) {
                throw new \LogicException('Unexpected hydration result');
            }

            yield $feed;
        }
    }
}
