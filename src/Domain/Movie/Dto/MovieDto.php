<?php

declare(strict_types=1);

namespace Domain\Movie\Dto;

class MovieDto
{
    public ?string $title;

    public ?string $link;

    public ?string $description;

    public \DateTimeImmutable $pubDate;

    public ?string $image = null;
}
