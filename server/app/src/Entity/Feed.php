<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Trait\DatedResourceTrait;
use App\Repository\FeedRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: FeedRepository::class)]
#[ORM\Table(name: 'feeds')]
#[UniqueEntity(
    fields: ['name'],
    message: 'This name is already used for an existing feed.'
)]
#[ApiResource(
    shortName: 'Feed',
    operations: [
        new Get(security: 'is_granted("ROLE_USER")'),
        new GetCollection(security: 'is_granted("ROLE_USER")'),
        new Post(security: 'is_granted("ROLE_USER")'),
        new Patch(security: 'is_granted("ROLE_USER")'),
        new Delete(security: 'is_granted("ROLE_USER")'),
    ],
    extraProperties: ['standard_put' => true]
)]
class Feed
{
    use DatedResourceTrait;

    /** @var ArrayCollection<int, Page> $pages */
    #[ORM\OneToMany(targetEntity: Page::class, mappedBy: 'feed', orphanRemoval: true)]
    private Collection $pages;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT, unique: true, nullable: false)]
    #[ApiProperty(readable: true, writable: false)]
    public ?int $id = null {
        get => $this->id;
        set {
            if (null !== $this->id) {
                throw new \LogicException('ID is already set and cannot be changed.');
            }

            $this->id = $value;
        }
    }

    #[ORM\Column(type: Types::STRING, nullable: false)]
    #[ApiProperty(readable: true, writable: true)]
    public string $name;

    #[ORM\Column(type: Types::STRING, unique: true, nullable: false)]
    #[ApiProperty(readable: true, writable: true)]
    public string $url;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[ApiProperty(readable: true, writable: true)]
    public ?\DateTime $lastFetchedAt = null;

    public function __construct()
    {
        $this->pages = new ArrayCollection();
    }

    /**
     * @return Collection<int, Page>
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }
}
