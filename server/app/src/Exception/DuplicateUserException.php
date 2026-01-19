<?php

declare(strict_types=1);

namespace App\Exception;

use App\Entity\User;

class DuplicateUserException extends \Exception
{
    public function __construct(User $user)
    {
        parent::__construct("A user with the email '{$user->getEmail()}' already exists.");
    }
}
