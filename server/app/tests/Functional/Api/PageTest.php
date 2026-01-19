<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Enum\PageStatus;
use App\Tests\Tools\FunctionalTestingToolTrait;

class PageTest extends ApiTestCase
{
    use FunctionalTestingToolTrait;

    public static function setUpBeforeClass(): void
    {
        static::$alwaysBootKernel = false; // @TODO to remove when migration to API Platform 5.0
    }

    public function testSecurity(): void
    {
        $urls = [
            ['url' => '/api/pages', 'method' => 'GET', 'content-type' => self::STANDARD_CONTENT_TYPE],
            ['url' => '/api/pages/1', 'method' => 'GET', 'content-type' => self::STANDARD_CONTENT_TYPE],
            ['url' => '/api/pages/1', 'method' => 'PATCH', 'content-type' => self::PATCH_CONTENT_TYPE],
            ['url' => '/api/pages/1', 'method' => 'DELETE', 'content-type' => self::STANDARD_CONTENT_TYPE],
            ['url' => '/api/pages', 'method' => 'POST', 'content-type' => self::STANDARD_CONTENT_TYPE],
        ];

        $client = static::createClient();

        foreach ($urls as $url) {
            $client->request($url['method'], $url['url'], ['headers' => ['Content-Type' => $url['content-type']]]);
            $this->assertResponseStatusCodeSame(401);
        }
    }

    public function testGetOnePageNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/pages/9999', ['headers' => self::getStandardHeaders()]);
        $this->assertResponseStatusCodeSame(404);

        $this->assertJsonContains([
            'detail' => 'Not Found',
        ]);
    }

    public function testGetOnePage(): void
    {
        $client = static::createClient();
        $response = $client->request('GET', '/api/pages/1', ['headers' => self::getStandardHeaders()]);
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', self::STANDARD_CONTENT_TYPE);

        $data = $response->toArray(false);

        $this->assertArrayHasKey('id', $data);
        static::assertSame(1, $data['id']);

        $this->assertArrayHasKey('url', $data);
        static::assertSame('https://www.dynamic-mess.com/windows/icone-wamp-orange-1-14/', $data['url']);

        $this->assertArrayHasKey('title', $data);
        static::assertSame('Souci avec Wamp', $data['title']);

        $this->assertArrayHasKey('description', $data);
        static::assertSame('Nous sommes en 2011 et vous avez un souci avec Wamp ? Regardez-ça !', $data['description']);

        $this->assertArrayHasKey('feed', $data);
        static::assertSame('/api/feeds/1', $data['feed']);

        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('processedAt', $data);
    }

    public function testCannotPostOnePageWithBadStatus(): void
    {
        $page = [
            'feed' => '/api/feeds/1',
            'url' => 'https://www.dynamic-mess.com/foofoo',
            'status' => 'pues',
        ];

        $client = static::createClient();

        $postRequest = function (array $page, $client, array $headers) {
            return $client->request(
                'POST',
                '/api/pages',
                [
                    'headers' => $headers,
                    'json' => $page,
                ]
            );
        };

        $postRequest($page, $client, self::getStandardHeaders());
        $this->assertResponseStatusCodeSame(400);
    }

    public function testGetOldestWaitingForDecision(): void
    {
        $client = static::createClient();
        $response = $client->request('GET', '/api/pages?status=PENDING', ['headers' => self::getStandardHeaders()]);
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', self::STANDARD_CONTENT_TYPE);

        $data = $response->toArray(false);
        $this->assertArrayHasKey('member', $data);
        $data = $data['member'];
        static::assertCount(1, $data);
        $data = $data[0];

        $this->assertArrayHasKey('id', $data);
        static::assertSame(3, $data['id']);

        $this->assertArrayHasKey('url', $data);
        static::assertSame('https://www.dynamic-mess.com/virtualisation/differents-parametres-connexion-virtualbox/', $data['url']);

        $this->assertArrayHasKey('title', $data);
        static::assertSame('Paramétrer VirtualBox', $data['title']);

        $this->assertArrayHasKey('description', $data);
        static::assertSame("Trop d'options dans Virtual Box !", $data['description']);

        $this->assertArrayHasKey('feed', $data);
        static::assertSame('/api/feeds/2', $data['feed']);

        $this->assertArrayNotHasKey('processedAt', $data);
    }

    public function testGetOldestToRead(): void
    {
        $client = static::createClient();
        $response = $client->request('GET', '/api/pages?status=TO_READ', ['headers' => self::getStandardHeaders()]);
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', self::STANDARD_CONTENT_TYPE);

        $data = $response->toArray(false);
        $this->assertArrayHasKey('member', $data);
        $data = $data['member'];
        static::assertCount(1, $data);
        $data = $data[0];

        $this->assertArrayHasKey('id', $data);
        static::assertSame(2, $data['id']);

        $this->assertArrayHasKey('url', $data);
        static::assertSame('https://www.dynamic-mess.com/reseau/nra-dslam-degroupage-18-99/', $data['url']);

        $this->assertArrayHasKey('title', $data);
        static::assertSame('Le jargon des FAI', $data['title']);

        $this->assertArrayHasKey('description', $data);
        static::assertSame('Votre connexion ne marche plus et vous ne comprenez rien à ce que vous dit le technicien !', $data['description']);

        $this->assertArrayHasKey('feed', $data);
        static::assertSame('/api/feeds/1', $data['feed']);

        $this->assertArrayNotHasKey('processedAt', $data);
    }

    public function testGetOldestToSummarize(): void
    {
        $client = static::createClient();
        $response = $client->request('GET', '/api/pages?status=TO_SUMMARIZE', ['headers' => self::getStandardHeaders()]);
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', self::STANDARD_CONTENT_TYPE);

        $data = $response->toArray(false);
        $this->assertArrayHasKey('member', $data);
        $data = $data['member'];
        static::assertCount(1, $data);
        $data = $data[0];

        $this->assertArrayHasKey('id', $data);
        static::assertSame(4, $data['id']);

        $this->assertArrayHasKey('url', $data);
        static::assertSame('https://www.dynamic-mess.com/foo/bar/', $data['url']);

        $this->assertArrayHasKey('title', $data);
        static::assertSame('Foo Bar', $data['title']);

        $this->assertArrayHasKey('description', $data);
        static::assertSame('Voici une page !', $data['description']);

        $this->assertArrayHasKey('feed', $data);
        static::assertSame('/api/feeds/1', $data['feed']);

        $this->assertArrayNotHasKey('processedAt', $data);
    }

    public function testCrud(): void
    {
        // Create a page.

        $page = [
            'feed' => '/api/feeds/1',
            'url' => 'https://www.dynamic-mess.com/foofoo',
            'status' => PageStatus::TO_READ->value,
            'title' => 'Pues',
            'description' => 'Pues Pues',
            'publishedAt' => '2021-01-27T10:57:36+00:00',
        ];

        $client = static::createClient();

        $postRequest = function (array $page, $client, array $headers) {
            return $client->request(
                'POST',
                '/api/pages',
                [
                    'headers' => $headers,
                    'json' => $page,
                ]
            );
        };

        $response = $postRequest($page, $client, self::getStandardHeaders());
        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', self::STANDARD_CONTENT_TYPE);

        $validateGetResponse = function (array $page, array $responseData) {
            static::assertEquals($page['feed'], $responseData['feed']);
            static::assertEquals($page['url'], $responseData['url']);
            static::assertEquals($page['title'], $responseData['title']);
            static::assertEquals($page['description'], $responseData['description']);
            static::assertEquals($page['status'], $responseData['status']);
            static::assertArrayHasKey('id', $responseData);
            static::assertArrayHasKey('createdAt', $responseData);
            static::assertArrayHasKey('updatedAt', $responseData);
            static::assertArrayHasKey('loadedAt', $responseData);
            static::assertArrayHasKey('publishedAt', $responseData);
            static::assertArrayNotHasKey('processedAt', $responseData);
            $this->assertMatchesRegularExpression('~^/api/pages/\d+$~', $responseData['@id']);
        };

        $responseData = $response->toArray(false);
        $validateGetResponse($page, $responseData);
        $resourceId = $responseData['id'];

        // Test we can GET it.

        $response = $client->request('GET', '/api/pages/'.$resourceId, ['headers' => self::getStandardHeaders()]);
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', self::STANDARD_CONTENT_TYPE);
        $validateGetResponse($page, $response->toArray(false));

        // Test we can't create it again.

        $postRequest($page, $client, self::getStandardHeaders());
        $this->assertResponseStatusCodeSame(422);

        // Test we can PATCH it.

        $page['toSummarize'] = true;
        $response = $client->request(
            'PATCH',
            '/api/pages/'.$resourceId,
            [
                'headers' => self::getPatchHeaders(),
                'json' => $page,
            ]
        );

        $this->assertResponseStatusCodeSame(200);
        $validateGetResponse($page, $response->toArray(false));

        // Test we can DELETE it.

        $client->request(
            'DELETE',
            '/api/pages/'.$resourceId,
            [
                'headers' => self::getStandardHeaders(),
            ]
        );

        $this->assertResponseStatusCodeSame(204);

        // Test we cannot GET it anymore.

        $client->request('GET', '/api/pages/'.$resourceId, ['headers' => self::getStandardHeaders()]);
        $this->assertResponseStatusCodeSame(404);
    }
}
