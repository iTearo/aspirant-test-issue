<?php

declare(strict_types=1);

namespace App\Repository\Movie;

use App\Entity\Movie;
use App\Exception\NotFoundException;
use Doctrine\ORM\EntityRepository;

class DoctrineMovieRepository extends EntityRepository implements MovieRepository
{
    /**
     * @return Movie[]
     */
    public function getAll(): array
    {
        return (array) $this->findAll();
    }

    public function getById(int $id): ?Movie
    {
        return $this->find($id);
    }

    /**
     * @param int $id
     * @return Movie
     * @throws NotFoundException
     */
    public function getByIdOrFail(int $id): Movie
    {
        if ($movie = $this->getById($id)) {
            return $movie;
        }

        throw new NotFoundException(sprintf('Movie not found by id #%d', $id));
    }

    public function getByTitle(string $title): ?Movie
    {
        return $this->findOneBy(['title' => $title]);
    }

    public function save(Movie $movie): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->getEntityManager()->persist($movie);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->getEntityManager()->flush($movie);
    }
}
