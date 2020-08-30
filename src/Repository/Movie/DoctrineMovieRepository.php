<?php

declare(strict_types=1);

namespace App\Repository\Movie;

use App\Entity\Movie;
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
