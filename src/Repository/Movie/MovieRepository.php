<?php

declare(strict_types=1);

namespace App\Repository\Movie;

use App\Entity\Movie;

interface MovieRepository
{
    /**
     * @return Movie[]
     */
    public function getAll(): array;

    public function getByTitle(string $title): ?Movie;

    public function save(Movie $movie): void;
}
