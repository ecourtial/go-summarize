<?php

declare(strict_types=1);

namespace App\Entity\Trait;

use ApiPlatform\Metadata\ApiProperty;
use Doctrine\ORM\Mapping as ORM;

trait DatedResourceTrait
{
    /** TO WORK, your entity must have the following annotation: #[ORM\HasLifecycleCallbacks] */
    #[ORM\Column(type: 'datetime', nullable: true)]
    #[ApiProperty(readable: true, writable: false)]
    public \DateTime $updatedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    #[ApiProperty(readable: true, writable: false)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ApiProperty(description: 'Datetime when the object was loaded from DB.', readable: true, writable: false)]
    private ?\DateTimeImmutable $loadedAt = null;

    public function getLoadedAt(): ?\DateTimeImmutable
    {
        return $this->loadedAt;
    }

    #[ORM\PostLoad()]
    public function setLoadedAt(): static
    {
        if (null === $this->loadedAt) {
            $this->loadedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist()]
    public function setCreatedAt(): static
    {
        if (null === $this->createdAt) {
            $this->createdAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate()]
    #[ORM\PrePersist()]
    public function setUpdatedAt(): static
    {
        $this->updatedAt = new \DateTime();

        return $this;
    }
}
