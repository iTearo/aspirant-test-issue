<?php

declare(strict_types=1);

namespace Lib\AppleTrailers\Dto;

class Trailer
{
    public string $title;

    public string $link;

    public string $description;

    public \DateTimeImmutable $pubDate;

    public ?string $image = null;
}
