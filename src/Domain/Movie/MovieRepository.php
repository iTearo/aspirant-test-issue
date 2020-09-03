<?php

declare(strict_types=1);

namespace Domain\Movie;

use Domain\Movie\Movie;
use Domain\Exception\NotFoundException;

interface MovieRepository
{
    /**
     * @return Movie[]
     */
    public function getAll(): array;

    public function getById(int $id): ?Movie;

    /**
     * @param int $id
     * @return Movie
     * @throws NotFoundException
     */
    public function getByIdOrFail(int $id): Movie;

    public function getByTitle(string $title): ?Movie;

    public function save(Movie $movie): void;
}
