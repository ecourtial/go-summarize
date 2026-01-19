<?php

namespace App\Tests\Tools;

use App\DataFixtures\AppFixtures;
use App\Entity\ApiToken;
use App\Repository\UserRepository;
use App\Security\TokenService;
use Doctrine\ORM\EntityManagerInterface;

trait FunctionalTestingToolTrait
{
    private const string STANDARD_CONTENT_TYPE = 'application/ld+json; charset=utf-8';
    private const string PATCH_CONTENT_TYPE = 'application/merge-patch+json; charset=utf-8';

    private const string FAKE_PLAIN_API_TOKEN = 'fake-fake-api-token';

    private static function getStandardHeaders(): array
    {
        return [
            'Content-Type' => self::STANDARD_CONTENT_TYPE,
            'Authorization' => 'Bearer '.AppFixtures::FIXTURE_USER_TOKEN,
        ];
    }

    private static function getPatchHeaders(): array
    {
        return [
            'Content-Type' => self::PATCH_CONTENT_TYPE,
            'Authorization' => 'Bearer '.AppFixtures::FIXTURE_USER_TOKEN,
        ];
    }

    public function createExpiredUserApiToken(string $userEmail): ApiToken
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => $userEmail]);

        // Create an expired token.

        /** @var TokenService $tokenService */
        $tokenService = self::getContainer()->get(TokenService::class);
        $tokenService->addTokenForUser($user, 'USER UPDATE ON LOGIN', self::FAKE_PLAIN_API_TOKEN);
        $userRepository->save($user, true);
        $createdToken = $user->getApiTokens()->last();

        $now = new \DateTimeImmutable();
        $limitDuration = ApiToken::TOKEN_MAX_VALIDITY_DAYS + 1;
        $limitDate = $now->modify("-$limitDuration days");

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->createQueryBuilder()
            ->update(ApiToken::class, 't')
            ->set('t.createdAt', ':createdAt')
            ->where('t.id = :id')
            ->setParameter('id', $createdToken->getId())
            ->setParameter('createdAt', $limitDate)
            ->getQuery()
            ->execute();

        return $createdToken;
    }
}
