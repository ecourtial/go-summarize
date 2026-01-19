<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\ApiToken;
use App\Entity\User;
use App\Repository\ApiTokenRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class TokenService
{
    public function __construct(
        #[Autowire('%env(APP_TOKEN_PEPPER)%')]
        private string $appTokenPepper,
        private readonly ApiTokenRepository $apiTokenRepository,
    ) {
    }

    public function addTokenForUser(User $user, string $description, ?string $plainApiToken = null): void
    {
        if (null === $plainApiToken) { // Standard use case. Manually setting the token is only for fixtures.
            $plainApiToken = 'tk_'.bin2hex(random_bytes(32));
        }

        $apiToken = new ApiToken($user, $this->hash($plainApiToken), $description);
        $user->addApiToken($apiToken);
        $user->lastCreatedPlainApiToken = $plainApiToken;
    }

    public function hash(string $token): string
    {
        return hash_hmac('sha512', $token, $this->appTokenPepper);
    }

    public function deleteOutdatedApiTokens(): void
    {
        $this->apiTokenRepository->deleteOutdatedApiTokens();
    }
}
