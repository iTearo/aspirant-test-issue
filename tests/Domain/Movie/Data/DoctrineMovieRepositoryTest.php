<?php

declare(strict_types=1);

namespace Domain\Movie\Data;

use Domain\Exception\NotFoundException;
use Domain\Movie\Movie;
use Domain\Movie\MovieRepository;
use TestTools\TestCase;

class DoctrineMovieRepositoryTest extends TestCase
{
    public function test_insert(): void
    {
        // Arrange
        $movie = $this->createMovie();
        $movie->setTitle($title = 'some_title')
            ->setDescription($description = 'some_description')
            ->setLink($link = 'some_link')
            ->setImage($image = 'some_image')
            ->setPubDate($pubDate = new \DateTimeImmutable())
        ;

        $repository = $this->getMovieRepository();

        // Act
        $repository->save($movie);

        // Assert
        self::assertNotNull($movie->getId());
        self::assertEquals($title, $movie->getTitle());
        self::assertEquals($description, $movie->getDescription());
        self::assertEquals($link, $movie->getLink());
        self::assertEquals($image, $movie->getImage());
        self::assertEquals($pubDate, $movie->getPubDate());
    }

    public function test_update(): void
    {
        // Arrange
        $movie = $this->createSavedMovie();
        $movie->setTitle($title = 'some_title')
            ->setDescription($description = 'some_description')
            ->setLink($link = 'some_link')
            ->setImage($image = 'some_image')
            ->setPubDate($pubDate = new \DateTimeImmutable())
        ;

        $repository = $this->getMovieRepository();

        // Act
        $repository->save($movie);

        // Assert
        self::assertEquals($title, $movie->getTitle());
        self::assertEquals($description, $movie->getDescription());
        self::assertEquals($link, $movie->getLink());
        self::assertEquals($image, $movie->getImage());
        self::assertEquals($pubDate, $movie->getPubDate());
    }

    public function test_getByTitle_movieFound(): void
    {
        // Arrange
        $movie = $this->createSavedMovie();

        $repository = $this->getMovieRepository();

        // Act
        $foundMovie = $repository->getByTitle($movie->getTitle());

        // Assert
        self::assertEquals($movie->getTitle(), $foundMovie->getTitle());
    }

    public function test_getByTitle_movieNotFound(): void
    {
        // Arrange
        $repository = $this->getMovieRepository();

        // Act
        $foundMovie = $repository->getByTitle('some_fake_title');

        // Assert
        self::assertNull($foundMovie);
    }

    public function test_getById_movieFound(): void
    {
        // Arrange
        $movie = $this->createSavedMovie();

        $repository = $this->getMovieRepository();

        // Act
        $foundMovie = $repository->getById($movie->getId());

        // Assert
        self::assertEquals($movie->getId(), $foundMovie->getId());
    }

    public function test_getById_movieNotFound(): void
    {
        // Arrange
        $repository = $this->getMovieRepository();

        // Act
        $foundMovie = $repository->getById(9999);

        // Assert
        self::assertNull($foundMovie);
    }

    public function test_getByIdOrFail_movieFound(): void
    {
        // Arrange
        $movie = $this->createSavedMovie();

        $repository = $this->getMovieRepository();

        // Act
        /** @noinspection PhpUnhandledExceptionInspection */
        $foundMovie = $repository->getByIdOrFail($movie->getId());

        // Assert
        self::assertEquals($movie->getId(), $foundMovie->getId());
    }

    public function test_getByIdOrFail_movieNotFound(): void
    {
        // Arrange
        $repository = $this->getMovieRepository();

        // Assert
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Movie not found by id #9999');

        // Act
        $repository->getByIdOrFail(9999);
    }

    protected function createMovie(): Movie
    {
        $movie = new Movie();
        $movie->setTitle('title')
            ->setDescription('description')
            ->setLink('link')
            ->setImage('image')
            ->setPubDate(new \DateTimeImmutable())
        ;
        return $movie;
    }

    protected function createSavedMovie(): Movie
    {
        $movie = $this->createMovie();
        $this->getMovieRepository()->save($movie);
        return $movie;
    }

    protected function getMovieRepository(): MovieRepository
    {
        return self::getContainer()->get(MovieRepository::class);
    }
}
