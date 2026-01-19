<?php

declare(strict_types=1);

namespace App\Tests\Functional\Console;

use App\Entity\Feed;
use App\Repository\FeedRepository;
use App\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class IngestFeedCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0">
	<channel>
		<title>Test RSS</title>
		<item>
			<title>Article 1</title>
			<description><p>A first article.</p></description>
			<link>https://www.foo.bar.com/1</link>
			<pubDate>2023-03-23T19:43:47+01:00</pubDate>
		</item>
        <item>
			<title>Article 2</title>
			<description>A second article.</description>
			<link>https://www.foo.bar.com/2</link>
			<pubDate>2023-03-24T19:43:47+01:00</pubDate>
		</item>
	</channel>
</rss>
XML;

        self::bootKernel();
        $application = new Application(self::$kernel);

        // Mock a response.
        $mock = new MockHttpClient([
            new MockResponse(
                $xml,
                [
                    'http_code' => 200,
                    'response_headers' => ['content-type' => 'application/xml'],
                ]
            ),
        ]);
        static::getContainer()->set(HttpClientInterface::class, $mock);

        // Set all the existing feeds as fetched so we will fetch the new one only.
        $feedRepository = static::getContainer()->get(FeedRepository::class);
        $feeds = $feedRepository->findAll();
        foreach ($feeds as $feed) {
            $feed->lastFetchedAt = new \DateTime('2099-03-23T19:43:47+01:00');
            $feedRepository->save($feed, true);
        }

        // Create the feed.
        $feed = new Feed();
        $feed->name = 'Foo';
        $feed->url = 'http://localhost/test/get-feed';
        $feed->lastFetchedAt = new \DateTime('2020-03-23T19:43:47+01:00');
        $feedRepository->save($feed, true);

        // URLs...
        $url1 = 'https://www.foo.bar.com/1';
        $url2 = 'https://www.foo.bar.com/2';

        $pagesRepository = static::getContainer()->get(PageRepository::class);
        static::assertNull($pagesRepository->findOneBy(['url' => $url1]));
        static::assertNull($pagesRepository->findOneBy(['url' => $url2]));

        // Launch the fetching.
        $command = $application->find('app:feed:ingest');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $commandTester->assertCommandIsSuccessful();

        static::assertNotNull($pagesRepository->findOneBy(['url' => $url1]));
        static::assertNotNull($pagesRepository->findOneBy(['url' => $url2]));
    }
}
