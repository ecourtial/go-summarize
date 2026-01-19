<?php

declare(strict_types=1);

namespace App\Tests\Functional\Console;

use App\Entity\Page;
use App\Enum\PageStatus;
use App\Repository\FeedRepository;
use App\Repository\PageRepository;
use App\Tests\Tools\FunctionalTestingToolTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class DeleteOldPagesCommandTest extends KernelTestCase
{
    use FunctionalTestingToolTrait;

    public function testDeleteOutdatedApiTokens(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $feedRepository = static::getContainer()->get(FeedRepository::class);
        $feed = $feedRepository->find(1);

        // Create an old page.
        $pageRepository = static::getContainer()->get(PageRepository::class);
        $outdatedPage = new Page();
        $outdatedPage->feed = $feed;
        $outdatedPage->url = 'Foo';
        $outdatedPage->title = 'Bar';
        $outdatedPage->description = 'Pues';
        $outdatedPage->status = PageStatus::WAITING_FOR_DECISION;
        $outdatedPage->publishedAt = new \DateTime();
        $pageRepository->save($outdatedPage, true);

        $now = new \DateTimeImmutable();
        $limitDuration = Page::PAGE_LIMIT_AGE + 1;
        $limitDate = $now->modify("-$limitDuration days");

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->createQueryBuilder()
            ->update(Page::class, 'p')
            ->set('p.createdAt', ':createdAt')
            ->where('p.id = :id')
            ->setParameter('id', $outdatedPage->id)
            ->setParameter('createdAt', $limitDate)
            ->getQuery()
            ->execute();

        // How many pages do we have?
        $pageCount = count($pageRepository->findAll());

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();

        // Run the command
        $command = $application->find('app:delete-old-pages');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $commandTester->assertCommandIsSuccessful();

        static::assertCount($pageCount - 1, $pageRepository->findAll());
        static::assertNull($pageRepository->find($outdatedPage->id));
    }
}
