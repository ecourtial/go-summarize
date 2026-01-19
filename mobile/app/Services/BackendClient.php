<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Page;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;

class BackendClient
{
    private const string PAGE_API_PATH = '/api/pages';

    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $token,
    ) {}

    private function baseRequest(bool $patch = false): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->withHeaders([
                'Content-Type' => $patch === true ? 'application/merge-patch+json; charset=utf-8' : 'application/ld+json; charset=utf-8',
                'Accept'       => 'application/ld+json',
            ]);
    }

    private function authedRequest(bool $patch = false): PendingRequest
    {
        if (!$this->token) {
            throw new \RuntimeException('No token set for authenticated request.');
        }

        return $this->baseRequest($patch)->withToken($this->token);
    }

    public function login(string $email, string $password): string
    {
        try {
            $resp = $this->baseRequest()->post(
                '/api/login',
                [
                    'email' => $email,
                    'password' => $password,
                ]
            );
        } catch (ConnectionException $e) {
            throw new \RuntimeException('Cannot reach server.');
        }

        $status = $resp->status();

        if ($status === 401) {
            throw new \RuntimeException('Invalid email or password.');
        }

        if ($status === 404) {
            throw new \RuntimeException('Login endpoint not found. Check server URL.');
        }

        if ($status >= 400 && $status < 500) {
            throw new \RuntimeException('Login failed (bad request).');
        }

        if ($status >= 500) {
            throw new \RuntimeException('Server error. Please try again later.');
        }

        $json = $resp->json();
        $token = $json['token'] ?? null;

        if (!is_string($token) || trim($token) === '') {
            throw new \RuntimeException('Login succeeded but no token was returned.');
        }

        return $token;
    }

    public function getOldestPendingItem(): Page|null
    {
        $resp = $this->authedRequest()->get(
            self::PAGE_API_PATH, [
                'status' => 'PENDING',
            ]
        );

        $this->checkForSessionExpired($resp->status());
        $resp->throw();

        $json = $resp->json();
        $data = is_array($json) ? $json : [];
        $memberKey = 'member';

        if (
            [] === $data
            || false === array_key_exists($memberKey, $data)
            || false === is_array($data[$memberKey])
            || 0 === count($data[$memberKey])
        ) {
            return null;
        }

        $item = $data['member'][0]; // @TODO we should either fetch only one item or store the list locally and loop on it.

        return new Page(
            $item['id'],
            $item['url'],
            $this->getFeedName($item['feed']),
            $item['title'],
            $item['description'],
            new \DateTime($item['publishedAt']),
        );
    }

    public function updatePageStatus(string $id, string $status): void
    {
        $resp = $this->authedRequest(true)->patch(
            self::PAGE_API_PATH."/$id",
            ['status' => $status,]
        );

        $this->checkForSessionExpired($resp->status());
        $resp->throw();
    }

    private function getFeedName(string $feedUri): string
    {
        $resp = $this->authedRequest()->get($feedUri);
        $this->checkForSessionExpired($resp->status());
        $resp->throw();

        $json = $resp->json();
        $data = is_array($json) ? $json : [];
        $feedNameKey = 'name';

        return array_key_exists($feedNameKey, $data) ? $data[$feedNameKey] : 'Unknown feed';
    }

    private function checkForSessionExpired(int $httpStatusCode): void
    {
        if (401 === $httpStatusCode) {
            throw new \RuntimeException('Session expired. Please sign in again.');
        }
    }
}
