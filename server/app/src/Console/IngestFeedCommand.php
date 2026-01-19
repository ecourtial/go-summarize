<?php

declare(strict_types=1);

namespace App\Console;

use App\Service\RssIngestService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

#[AsCommand(name: 'app:feed:ingest', description: 'Ingest feeds. Example, with option and optional feed id:  bin/console app:feed:ingest --force 1')]
#[AsCronTask('0 3 * * *')] // Every day at three AM.
final class IngestFeedCommand extends Command
{
    private const string FEED_ID_PARAM = 'feedId';
    private const string FORCE_OPTION = 'force';

    public function __construct(
        private readonly RssIngestService $service,
        private readonly LoggerInterface $feedLogger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(self::FEED_ID_PARAM, InputArgument::OPTIONAL, 'Only fetch the feed with this id.');
        $this->addOption(self::FORCE_OPTION, description: 'Ignore last fetch date of the feed(s): force fetch again.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->feedLogger->info('Starting command to ingest feeds.', []);

        $feedId = $input->getArgument(self::FEED_ID_PARAM);
        if (!is_int($feedId)) {
            $feedId = null;
        }

        $force = $input->getOption(self::FORCE_OPTION);
        $force = (bool) $force;

        if (true === $force) {
            $output->writeln('<info>Force mode enabled: all feeds will be fetched to look for new entries.</info>');
            $force = true;
        }

        $stats = $this->service->processFeeds($force, $feedId);

        $output->writeln(sprintf(
            'Done. URL created = %d URL skipped = %d Feed error = %s',
            $stats['urlCreated'],
            $stats['urlSkipped'],
            $stats['feedInError'],
        ));

        $this->feedLogger->info('Ingestion of feeds ended with success.', []);

        return Command::SUCCESS;
    }
}
