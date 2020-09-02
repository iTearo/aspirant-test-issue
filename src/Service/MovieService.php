<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\MovieDto;
use App\Entity\Movie;
use App\Exception\NotFoundException;
use App\Repository\Movie\MovieRepository;

class MovieService
{
    private MovieRepository $repository;

    public function __construct(
        MovieRepository $repository
    ) {
        $this->repository = $repository;
    }

    public function getByTitle(string $title): ?Movie
    {
        return $this->repository->getByTitle($title);
    }

    public function create(MovieDto $movieDto): ?Movie
    {
        $movie = new Movie();

        $this->fill($movie, $movieDto);

        $this->repository->save($movie);
        return $movie;
    }

    /**
     * @throws NotFoundException
     */
    public function update(int $movieId, MovieDto $movieDto): ?Movie
    {
        $movie = $this->repository->getByIdOrFail($movieId);

        $this->fill($movie, $movieDto);

        $this->repository->save($movie);
        return $movie;
    }

    protected function fill(Movie $movie, MovieDto $movieDto): void
    {
        $movie
            ->setTitle($movieDto->title)
            ->setDescription($movieDto->description)
            ->setLink($movieDto->link)
            ->setPubDate($movieDto->pubDate)
            ->setImage($movieDto->image)
        ;
    }
}
