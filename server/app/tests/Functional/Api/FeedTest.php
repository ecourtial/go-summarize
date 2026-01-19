<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Enum\PageStatus;
use App\Tests\Tools\FunctionalTestingToolTrait;

class FeedTest extends ApiTestCase
{
    use FunctionalTestingToolTrait;

    public static function setUpBeforeClass(): void
    {
        static::$alwaysBootKernel = false; // @TODO to remove when migration to API Platform 5.
    }

    public function testSecurity(): void
    {
        $urls = [
            ['url' => '/api/feeds', 'method' => 'GET', 'content-type' => self::STANDARD_CONTENT_TYPE],
            ['url' => '/api/feeds/1', 'method' => 'GET', 'content-type' => self::STANDARD_CONTENT_TYPE],
            ['url' => '/api/feeds/1', 'method' => 'PATCH', 'content-type' => self::PATCH_CONTENT_TYPE],
            ['url' => '/api/feeds/1', 'method' => 'DELETE', 'content-type' => self::STANDARD_CONTENT_TYPE],
            ['url' => '/api/feeds', 'method' => 'POST', 'content-type' => self::STANDARD_CONTENT_TYPE],
        ];

        $client = static::createClient();

        foreach ($urls as $url) {
            $client->request($url['method'], $url['url'], ['headers' => ['Content-Type' => $url['content-type']]]);
            $this->assertResponseStatusCodeSame(401);
        }
    }

    public function testGetOneFeedNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/feeds/9999', ['headers' => self::getStandardHeaders()]);
        $this->assertResponseStatusCodeSame(404);

        $this->assertJsonContains([
            'detail' => 'Not Found',
        ]);
    }

    public function testGetOneFeed(): void
    {
        $client = static::createClient();
        $response = $client->request('GET', '/api/feeds/1', ['headers' => self::getStandardHeaders()]);
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $data = $response->toArray(false);

        $this->assertArrayHasKey('id', $data);
        static::assertSame(1, $data['id']);

        $this->assertArrayHasKey('name', $data);
        static::assertSame('Dynamic-Mess', $data['name']);

        $this->assertArrayHasKey('url', $data);
        static::assertSame('https://www.dynamic-mess.com/rss.xml', $data['url']);

        $this->assertArrayHasKey('lastFetchedAt', $data);
    }

    public function testCrud(): void
    {
        // Create a feed.

        $feed = [
            'name' => 'foo',
            'url' => 'http://foo.bar',
        ];

        $client = static::createClient();

        $postRequest = function (array $feed, $client, array $headers) {
            return $client->request(
                'POST',
                '/api/feeds',
                [
                    'headers' => $headers,
                    'json' => $feed,
                ]
            );
        };

        $response = $postRequest($feed, $client, self::getStandardHeaders());
        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $validateGetResponse = function (array $feed, array $responseData) {
            static::assertEquals($feed['name'], $responseData['name']);
            static::assertEquals($feed['url'], $responseData['url']);
            $this->assertMatchesRegularExpression('~^/api/feeds/\d+$~', $responseData['@id']);
        };

        $responseData = $response->toArray(false);
        $validateGetResponse($feed, $responseData);
        static::assertArrayNotHasKey('lastFetchedAt', $responseData);
        $feedId = $responseData['id'];

        // Test we can GET the feed.

        $response = $client->request('GET', '/api/feeds/'.$feedId, ['headers' => self::getStandardHeaders()]);
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $validateGetResponse($feed, $response->toArray(false));

        // Test we can't create the feed again.

        $postRequest($feed, $client, self::getStandardHeaders());
        $this->assertResponseStatusCodeSame(422);

        // Test we can PATCH it.

        $feed['lastFetchedAt'] = '2026-01-27T10:57:36+00:00';
        $response = $client->request(
            'PATCH',
            '/api/feeds/'.$feedId,
            [
                'headers' => self::getPatchHeaders(),
                'json' => $feed,
            ]
        );

        $this->assertResponseStatusCodeSame(200);
        $responseData = $response->toArray(false);
        $validateGetResponse($feed, $responseData);
        static::assertEquals($feed['lastFetchedAt'], $responseData['lastFetchedAt']);

        // Add one URL to the feed

        $page = [
            'feed' => '/api/feeds/'.$feedId,
            'url' => 'https://www.dynamic-mess.com/foofoo',
            'status' => PageStatus::WAITING_FOR_DECISION,
            'title' => 'Pues',
            'description' => 'Pues Pues',
            'publishedAt' => '2021-01-27T10:57:36+00:00',
        ];

        $client = static::createClient();

        $pagePostRequest = function (array $page, $client, array $headers) {
            return $client->request(
                'POST',
                '/api/pages',
                [
                    'headers' => $headers,
                    'json' => $page,
                ]
            );
        };
        $pagePostResponse = $pagePostRequest($page, $client, self::getStandardHeaders());
        $this->assertResponseStatusCodeSame(201);
        $pageResponseData = $pagePostResponse->toArray(false);
        $pageId = $pageResponseData['id'];

        // Test we can get the URL and it is linked to the feed.

        $response = $client->request('GET', '/api/pages/'.$pageId, ['headers' => self::getStandardHeaders()]);
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', self::STANDARD_CONTENT_TYPE);
        $pageResponseData = $response->toArray(false);
        static::assertSame('/api/feeds/'.$feedId, $pageResponseData['feed']);

        // Test we can DELETE the feed.

        $client->request('DELETE', '/api/feeds/'.$feedId, ['headers' => self::getStandardHeaders()]);
        $this->assertResponseStatusCodeSame(204);

        // Test we cannot GET the feed anymore.

        $client->request('GET', '/api/feeds/'.$feedId, ['headers' => self::getStandardHeaders()]);
        $this->assertResponseStatusCodeSame(404);

        // Test that the URL has been deleted too.

        $client->request('GET', '/api/pages/'.$pageId, ['headers' => self::getStandardHeaders()]);
        $this->assertResponseStatusCodeSame(404);
    }
}
