<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\AppFixtures;
use App\Tests\Tools\FunctionalTestingToolTrait;

class UserTest extends ApiTestCase
{
    use FunctionalTestingToolTrait;

    public static function setUpBeforeClass(): void
    {
        static::$alwaysBootKernel = false; // @TODO to remove when migration to API Platform 5.0
    }

    public function testCannotCreateUser(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/users', ['headers' => self::getStandardHeaders()]);
        $this->assertResponseStatusCodeSame(404);

        $this->assertJsonContains([
            'detail' => 'No route found for "POST http://localhost/api/users"',
        ]);
    }

    public function testCannotPatchUser(): void
    {
        $client = static::createClient();
        $client->request('PATCH', '/api/users/1', ['headers' => self::getPatchHeaders()]);
        $this->assertResponseStatusCodeSame(404);

        $this->assertJsonContains([
            'detail' => 'No route found for "PATCH http://localhost/api/users/1"',
        ]);
    }

    public function testCannotGetUsers(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/users', ['headers' => self::getStandardHeaders()]);
        $this->assertResponseStatusCodeSame(404);

        $this->assertJsonContains([
            'detail' => 'No route found for "GET http://localhost/api/users"',
        ]);
    }

    public function testCannotGetUser(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/users/1', ['headers' => self::getStandardHeaders()]);
        $this->assertResponseStatusCodeSame(404);

        $this->assertJsonContains([
            'detail' => 'No route found for "GET http://localhost/api/users/1"',
        ]);
    }

    public function testCannotDeleteUser(): void
    {
        $client = static::createClient();
        $client->request('DELETE', '/api/users/1', ['headers' => self::getStandardHeaders()]);
        $this->assertResponseStatusCodeSame(404);

        $this->assertJsonContains([
            'detail' => 'No route found for "DELETE http://localhost/api/users/1"',
        ]);
    }

    public function testCannotLoginBecauseBadPassword(): void
    {
        $user = [
            'email' => AppFixtures::FIXTURE_USER_EMAIL,
            'password' => 'pues@pues123',
        ];

        $client = static::createClient();

        $postRequest = function (array $user, $client) {
            return $client->request(
                'POST',
                '/api/login',
                [
                    'headers' => [
                        'Content-Type' => self::STANDARD_CONTENT_TYPE,
                    ],
                    'json' => $user,
                ]
            );
        };

        $postRequest($user, $client);
        $this->assertResponseStatusCodeSame(401);
    }

    public function testCannotLoginBecauseBadEmail(): void
    {
        $user = [
            'email' => 'foo@barbar.com',
            'password' => AppFixtures::FIXTURE_USER_PASSWORD,
        ];

        $client = static::createClient();

        $postRequest = function (array $user, $client) {
            return $client->request(
                'POST',
                '/api/login',
                [
                    'headers' => [
                        'Content-Type' => self::STANDARD_CONTENT_TYPE,
                    ],
                    'json' => $user,
                ]
            );
        };

        $postRequest($user, $client);
        $this->assertResponseStatusCodeSame(401);
    }

    public function testLoginSuccessful(): void
    {
        $user = [
            'email' => AppFixtures::FIXTURE_USER_EMAIL,
            'password' => AppFixtures::FIXTURE_USER_PASSWORD,
        ];

        $client = static::createClient();

        $postRequest = function (array $user, $client) {
            return $client->request(
                'POST',
                '/api/login',
                [
                    'headers' => [
                        'Content-Type' => self::STANDARD_CONTENT_TYPE,
                    ],
                    'json' => $user,
                ]
            );
        };

        $response = $postRequest($user, $client);
        $this->assertResponseStatusCodeSame(200);

        $responseData = $response->toArray(false);
        static::assertCount(4, $responseData);
        static::assertArrayHasKey('id', $responseData);
        static::assertArrayHasKey('user', $responseData);
        static::assertArrayHasKey('email', $responseData);
        static::assertArrayHasKey('token', $responseData);
        static::assertNotEquals(AppFixtures::FIXTURE_USER_TOKEN, $responseData['user']);
        static::assertSame(1, $responseData['id']);
        static::assertSame(AppFixtures::FIXTURE_USER_EMAIL, $responseData['user']);
        static::assertSame(AppFixtures::FIXTURE_USER_EMAIL, $responseData['email']);
    }

    public function testCannotUseExpiredToken(): void
    {
        $apiToken = $this->createExpiredUserApiToken(AppFixtures::FIXTURE_USER_EMAIL);

        // User cannot use it
        $client = static::createClient();
        $client->request('GET', '/api/pages/1', ['headers' => [
            'Content-Type' => self::STANDARD_CONTENT_TYPE,
            'Authorization' => 'Bearer '.self::FAKE_PLAIN_API_TOKEN,
        ]]);
        $this->assertResponseStatusCodeSame(401);
    }
}
