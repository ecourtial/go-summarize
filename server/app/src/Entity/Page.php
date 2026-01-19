<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Trait\DatedResourceTrait;
use App\Enum\PageStatus;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity()]
#[ORM\Table(name: 'pages')]
#[UniqueEntity(
    fields: ['url'],
    message: 'This URL already exists.'
)]
#[ApiResource(
    shortName: 'Page',
    operations: [
        new Get(security: 'is_granted("ROLE_USER")'),
        new GetCollection(security: 'is_granted("ROLE_USER")'),
        new Post(security: 'is_granted("ROLE_USER")'),
        new Patch(security: 'is_granted("ROLE_USER")'),
        new Delete(security: 'is_granted("ROLE_USER")'),
    ],
    extraProperties: ['standard_put' => true]
)]
#[ApiFilter(SearchFilter::class, properties: ['status'])]
#[ApiFilter(DateFilter::class, properties: ['processedAt'])]
#[ApiFilter(ExistsFilter::class, properties: ['processedAt'])]
class Page
{
    use DatedResourceTrait;

    public const int PAGE_LIMIT_AGE = 90;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT, unique: true, nullable: false)]
    public ?int $id = null {
        get => $this->id;
        set {
            if (null !== $this->id) {
                throw new \LogicException('ID is already set and cannot be changed.');
            }

            $this->id = $value;
        }
    }

    #[ORM\ManyToOne(targetEntity: Feed::class, inversedBy: 'pages')]
    #[ApiProperty(readable: true, writable: true)]
    public Feed $feed;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    #[ApiProperty(readable: true, writable: true)]
    public \DateTime $publishedAt;

    #[ORM\Column(type: Types::STRING, unique: true, nullable: false)]
    #[ApiProperty(readable: true, writable: true)]
    public string $url;

    #[ORM\Column(type: Types::STRING, unique: true, nullable: false)]
    #[ApiProperty(readable: true, writable: true)]
    public string $title;

    #[ORM\Column(type: Types::STRING, nullable: false)]
    #[ApiProperty(readable: true, writable: true)]
    public string $description;

    #[ORM\Column(type: Types::ENUM, nullable: false, enumType: PageStatus::class)]
    #[Assert\NotNull]
    public PageStatus $status;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[ApiProperty(readable: true, writable: true)]
    public \DateTime $processedAt;
}
