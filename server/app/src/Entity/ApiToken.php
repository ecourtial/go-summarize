<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\DatedResourceTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'api_token')]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'uniq_api_token_hash', columns: ['token_hash'])]
class ApiToken
{
    use DatedResourceTrait;
    public const int TOKEN_MAX_VALIDITY_DAYS = 90;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    private string $tokenHash;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: false)]
    private string $description;

    public function __construct(User $user, string $tokenHash, string $description)
    {
        $this->user = $user;
        $this->tokenHash = $tokenHash;
        $this->description = $description;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTokenHash(): string
    {
        return $this->tokenHash;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
