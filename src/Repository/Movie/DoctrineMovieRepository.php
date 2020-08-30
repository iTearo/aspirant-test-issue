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
}
