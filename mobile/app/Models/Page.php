<?php
declare(strict_types=1);

namespace App\Models;

readonly class Page
{
    public function __construct(
        public int $id,
        public string $url,
        public string $feedName,
        public string $title,
        public string $description,
        public \DateTime $publishedAt,
    ) {
    }
}
