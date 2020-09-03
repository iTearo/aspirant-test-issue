<?php

declare(strict_types=1);

namespace Domain\Movie\Data;

use Domain\Exception\NotFoundException;
use Doctrine\ORM\EntityRepository;
use Domain\Movie\Movie;
use Domain\Movie\MovieRepository;

class DoctrineMovieRepository extends EntityRepository implements MovieRepository
{
    public const TABLE = 'movie';

    /**
     * @return Movie[]
     */
    public function getAll(): array
    {
        return (array) $this->findAll();
    }

    public function getById(int $id): ?Movie
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
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
        /** @noinspection PhpIncompatibleReturnTypeInspection */
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
