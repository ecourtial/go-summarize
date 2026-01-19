<?php

declare(strict_types=1);

namespace App\Console;

use App\Repository\PageRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

#[AsCommand(name: 'app:delete-old-pages', description: 'Delete outdated API tokens.')]
#[AsCronTask('0 5 * * *')] // Every day at five AM.
class DeleteOldPagesCommand extends Command
{
    public function __construct(private readonly PageRepository $pageRepository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->pageRepository->deleteOldPages();

        return Command::SUCCESS;
    }
}
