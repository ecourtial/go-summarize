<?php
declare(strict_types=1);

namespace App\Services;

class AuthStore
{
    private const string SERVER_URL_STORAGE_KEY = 'serverUrl';
    private const string SERVER_USER_TOKEN_STORAGE_KEY = 'userToken';

    private string $path;

    public function __construct()
    {
        // This is inside the app's sandbox; should persist across app restarts.
        $this->path = storage_path('app/auth_session.json');
    }

    public function isLoggedIn(): bool
    {
        return ($this->getServerUrl() && $this->getToken());
    }

    public function getServerUrl(): ?string
    {
        return $this->read()[self::SERVER_URL_STORAGE_KEY] ?? null;
    }

    public function getToken(): ?string
    {
        return $this->read()[self::SERVER_USER_TOKEN_STORAGE_KEY] ?? null;
    }

    public function set(string $serverUrl, string $token): void
    {
        $payload = [
            self::SERVER_URL_STORAGE_KEY => rtrim(trim($serverUrl), '/'),
            self::SERVER_USER_TOKEN_STORAGE_KEY => trim($token),
        ];

        $dir = dirname($this->path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        file_put_contents($this->path, json_encode($payload, JSON_PRETTY_PRINT));
    }

    public function clear(): void
    {
        if (is_file($this->path)) {
            @unlink($this->path);
        }
    }

    private function read(): array
    {
        if (!is_file($this->path)) {
            return [];
        }

        $raw = file_get_contents($this->path);
        $data = json_decode($raw ?: '', true);

        return is_array($data) ? $data : [];
    }
}
