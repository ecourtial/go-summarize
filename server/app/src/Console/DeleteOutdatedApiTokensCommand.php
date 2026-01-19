<?php

declare(strict_types=1);

namespace App\Console;

use App\Security\TokenService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

#[AsCommand(name: 'app:delete-outdated-api-tokens', description: 'Delete outdated API tokens.')]
#[AsCronTask('0 6 * * *')] // Every day at six AM.
class DeleteOutdatedApiTokensCommand extends Command
{
    public function __construct(private readonly TokenService $tokenService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->tokenService->deleteOutdatedApiTokens();

        return Command::SUCCESS;
    }
}
