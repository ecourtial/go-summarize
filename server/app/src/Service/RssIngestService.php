<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Feed;
use App\Entity\Page;
use App\Enum\PageStatus;
use App\Repository\FeedRepository;
use App\Repository\PageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class RssIngestService
{
    private const int MAX_LENGTH = 255;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly FeedRepository $feedRepository,
        private readonly PageRepository $pageRepository,
        private readonly HtmlSanitizerInterface $sanitizer,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $feedLogger,
    ) {
    }

    /** @return array<string,int> */
    public function processFeeds(bool $force = false, ?int $feedId = null): array
    {
        $results = ['urlCreated' => 0, 'urlSkipped' => 0, 'feedInError' => 0];
        $today = new \DateTime();
        $ignoreAlreadyFetchedFeeds = !(true === $force); // If we force, we don't ignore already fetched feeds!

        // Loop on each feed.
        foreach ($this->feedRepository->findFeedsToFetchForNews($today, $ignoreAlreadyFetchedFeeds, $feedId) as $feed) {
            try {
                $now = new \DateTime();
                $this->logInfoForFeed($feed);
                $resultForFeed = $this->ingest($feed, $force);
                $feed->lastFetchedAt = $now;
                $this->feedRepository->save($feed, true);

                $results['urlCreated'] += $resultForFeed['urlCreated'];
                $results['urlSkipped'] += $resultForFeed['urlSkipped'];
                $this->entityManager->clear(); // Because we may have created a lot of URL objects.
            } catch (\Throwable $exception) {
                $this->feedLogger->error($exception->getMessage());
                ++$results['feedInError'];
            }
        }

        return $results;
    }

    private function logInfoForFeed(Feed $feed): void
    {
        $this->feedLogger->info('Processing feed', [
            'id' => $feed->id,
            'name' => $feed->name,
            'url' => $feed->url,
        ]);
    }

    /**
     * @return array{urlCreated:int, urlSkipped:int}
     */
    private function ingest(Feed $targetFeed, bool $force): array
    {
        $response = $this->httpClient->request(
            'GET',
            $targetFeed->url,
            [
                'headers' => [
                    'Accept' => 'application/rss+xml, application/atom+xml, application/xml, text/xml;q=0.9, */*;q=0.1',
                    'User-Agent' => 'GoSummarize/1.0',
                ],
                'timeout' => 5, // According to the doc: "...the maximum total duration of the request, including DNS, connect, TLS, redirects, and reading the response body."
                'max_duration' => 5,
                'max_redirects' => 1,
            ]
        );

        $xml = $response->getContent(); // throws on 4xx/5xx

        // Defensive parsing: avoid warnings, handle invalid XML cleanly.
        $prev = libxml_use_internal_errors(true);
        $root = simplexml_load_string($xml);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        if (false === $root) {
            throw new \RuntimeException('Invalid XML (RSS/Atom) received from feed: '.$targetFeed->url);
        }

        $created = 0;
        $skipped = 0;

        foreach ($this->extractEntries($root) as [$url, $title, $description, $pubDate]) {
            $this->feedLogger->info("Processing URL '$url' for feed #{$targetFeed->id}", []);

            $url = $this->normalizeUrl($url);
            $title = $this->normalizeTitle($title);
            $description = $this->normalizeDescription($description);

            if (null === $url || null === $title || null === $pubDate) {
                ++$skipped;
                continue;
            }

            /*
             * Standard behavior: ignore this URL.
             * Otherwise, we continue and will check if we already have it in the DB.
             */
            if (null !== $targetFeed->lastFetchedAt && false === $force && $pubDate < $targetFeed->lastFetchedAt) {
                $this->feedLogger->info("Skipping URL '$url' for feed #{$targetFeed->id} because of the publication date.", []);

                continue;
            }

            $existing = $this->pageRepository->findOneByUrl($url);

            if (null === $existing) {
                $page = new Page();
                $page->url = $url;
                $page->title = $title;
                $page->description = $description;
                $page->status = PageStatus::WAITING_FOR_DECISION;
                $page->feed = $targetFeed;
                $page->publishedAt = $pubDate;

                $this->pageRepository->save($page, true);
                ++$created;
            } else {
                /*
                 * Ignore, we already have it. Might happen because of hours and minutes.
                 */
                ++$skipped;
                $this->feedLogger->info("Skipping URL '$url' for feed #{$targetFeed->id} because already exists in the DB.", []);
            }
        }

        return ['urlCreated' => $created, 'urlSkipped' => $skipped];
    }

    /**
     * @return \Generator<array{0:string,1:string,2:string,3:\DateTime|null}>
     */
    private function extractEntries(\SimpleXMLElement $root): \Generator
    {
        // RSS 2.0: <rss><channel><item>...
        if (isset($root->channel->item)) {
            foreach ($root->channel->item as $item) {
                $url = (string) ($item->link ?? '');
                $title = (string) ($item->title ?? '');
                $desc = $this->getInnerXmlOrText($item->description);
                $desc = $this->normalizeDescription($desc);
                $pubDate = (string) ($item->pubDate ?? '');
                $pubDate = '' === $pubDate ? null : new \DateTime($pubDate);
                yield [$url, $title, $desc, $pubDate];
            }
        } elseif (isset($root->entry)) { // Atom: <feed><entry>...
            foreach ($root->entry as $entry) {
                $title = (string) ($entry->title ?? '');

                $desc = (string) ($entry->summary ?? '');
                if ('' === $desc) {
                    $desc = (string) ($entry->content ?? '');
                }

                $pubDate = (string) ($entry->updated ?? '');
                $pubDate = '' === $pubDate ? null : new \DateTime($pubDate);

                // Atom links are attributes: <link href="..."/>
                $url = '';
                if (isset($entry->link)) {
                    // Try rel="alternate" first
                    $chosen = null;
                    foreach ($entry->link as $link) {
                        $rel = (string) ($link['rel'] ?? '');
                        if ('' === $rel || 'alternate' === $rel) {
                            $chosen = $link;
                            break;
                        }
                    }
                    $url = (string) ($chosen?->attributes()->href ?? '');
                }

                yield [$url, $title, $desc, $pubDate];
            }
        } else {
            // Some feeds are RDF-ish or otherwise nested: try XPath fallback
            $items = $root->xpath('//item') ?: [];
            if ($items) {
                foreach ($items as $item) {
                    $url = (string) ($item->link ?? '');
                    $title = (string) ($item->title ?? '');
                    $desc = (string) ($item->description ?? '');
                    $pubDate = (string) ($item->pubDate ?? '');
                    $pubDate = '' === $pubDate ? null : new \DateTime($pubDate);
                    yield [$url, $title, $desc, $pubDate];
                }
            }
        }

        return [];
    }

    private function getInnerXmlOrText(?\SimpleXMLElement $node): string
    {
        if (null === $node) {
            return '';
        }

        // If it has no child elements, plain text/cdata casting is fine
        if (0 === $node->count()) {
            return (string) $node;
        }

        // Otherwise build inner XML from children (keeps tags)
        $xml = '';
        foreach ($node->children() as $child) {
            $xml .= $child->asXML() ?: '';
        }

        // Fallback: try to remove wrapper tag from asXML()
        if ('' === $xml) {
            $full = $node->asXML() ?: '';
            $xml = preg_replace('~^<[^>]+>|</[^>]+>$~', '', $full) ?? '';
        }

        return $xml;
    }

    private function normalizeDescription(string $description): string
    {
        $description = trim($description);
        $description = str_replace('<![CDATA[', '', $description);
        $description = str_replace(']]>', '', $description);
        $description = $this->sanitizer->sanitize($description);
        $description = trim($description);

        $description = trim($description);

        if (mb_strlen($description) > self::MAX_LENGTH) {
            $description = mb_substr($description, 0, self::MAX_LENGTH - 3).'...';
        }

        return $this->replaceIfContainsImage($description);
    }

    private function normalizeUrl(string $url): ?string
    {
        $url = trim($url);
        if ('' === $url) {
            return null;
        }

        // Basic validation: must be http(s)
        if (!preg_match('~^https?://~i', $url)) {
            return null;
        }

        // Remove common junk whitespace, keep as-is otherwise
        return $url;
    }

    private function normalizeTitle(string $title): ?string
    {
        $title = trim(preg_replace('~\s+~u', ' ', $title) ?? '');
        if ('' === $title) {
            return null;
        }

        $title = $this->sanitizer->sanitize($title);
        $title = trim($title);

        // keep within column length (avoid DB errors)
        if (mb_strlen($title) > self::MAX_LENGTH) {
            $title = mb_substr($title, 0, self::MAX_LENGTH - 3).'...';
        }

        return $this->replaceIfContainsImage($title);
    }

    private function replaceIfContainsImage(string $html): string
    {
        if (false === stripos($html, '<img')) { // Won't work in you use the very old way in UPPERCASE.
            return $html;
        }

        return '';
    }
}
