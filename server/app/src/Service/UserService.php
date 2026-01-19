<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Exception\DuplicateUserException;
use App\Repository\UserRepository;
use App\Security\TokenService;
use Doctrine\DBAL\Exception\ConstraintViolationException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class UserService
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $userRepository,
        private TokenService $tokenService,
    ) {
    }

    public function createUser(string $email, string $plainPassword, ?string $plainToken = null): User
    {
        $user = new User();
        $user->setEmail($email);

        $this->hashPasswordForUser($user, $plainPassword);
        $this->tokenService->addTokenForUser($user, 'USER CREATION', $plainToken);

        try {
            $this->userRepository->save($user, true);
        } catch (ConstraintViolationException $exception) {
            if (str_contains($exception->getMessage(), "for key 'users.UNIQ_IDENTIFIER_EMAIL'")) {
                throw new DuplicateUserException($user);
            }
        }

        return $user;
    }

    public function updateUserTokenOnLogin(User $user): void
    {
        $this->tokenService->addTokenForUser($user, 'USER UPDATE ON LOGIN');
        $this->userRepository->save($user, true);
    }

    private function hashPasswordForUser(User $user, string $plainPassword): void
    {
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
    }
}
