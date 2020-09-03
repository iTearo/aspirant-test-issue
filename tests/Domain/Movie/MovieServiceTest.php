<?php

declare(strict_types=1);

namespace Domain\Movie;

use Domain\Movie\Dto\MovieDto;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class MovieServiceTest extends TestCase
{
    public function test_getByTitle(): void
    {
        // Arrange
        $movie = new Movie();
        $movie->setTitle($movieTitle = 'title');

        $repository = $this->prophesize(MovieRepository::class);
        $repository->getByTitle(Argument::is($movieTitle))->willReturn($movie);

        $movieService = new MovieService($repository->reveal());

        // Act
        $foundMovie = $movieService->getByTitle($movie->getTitle());

        // Assert
        self::assertEquals($movie->getTitle(), $foundMovie->getTitle());
    }

    public function test_fill(): void
    {
        // Arrange
        $movie = new Movie();
        $movieDto = $this->createMovieDto();

        $movieService = new MovieService(
            $this->prophesize(MovieRepository::class)->reveal()
        );

        $reflection = new \ReflectionMethod(MovieService::class, 'fill');
        $reflection->setAccessible(true);

        // Act
        $reflection->invokeArgs($movieService, [$movie, $movieDto]);

        // Assert
        self::assertEquals($movieDto->title,        $movie->getTitle());
        self::assertEquals($movieDto->description,  $movie->getDescription());
        self::assertEquals($movieDto->link,         $movie->getLink());
        self::assertEquals($movieDto->image,        $movie->getImage());
        self::assertEquals($movieDto->pubDate,      $movie->getPubDate());
    }

    public function test_create(): void
    {
        // Arrange
        $movieDto = $this->createMovieDto();

        $repository = $this->prophesize(MovieRepository::class);
        $saveMethod = $repository->save(Argument::type(Movie::class));

        $movieService = $this->getMockBuilder(MovieService::class)
            ->onlyMethods(['fill'])
            ->setConstructorArgs([$repository->reveal()])
            ->getMock();

        $movieService
            ->expects(self::once())
            ->method('fill');

        // Act
        /** @var MovieService $movieService */
        $savedMovie = $movieService->create($movieDto);

        // Assert
        $saveMethod->shouldBeCalled();
        self::assertInstanceOf(Movie::class, $savedMovie);
    }

    public function test_update(): void
    {
        // Arrange
        $movieId = 9999;
        $movie = new Movie();
        $movieDto = $this->createMovieDto();

        $repository = $this->prophesize(MovieRepository::class);
        $repository->getByIdOrFail(Argument::is($movieId))->willReturn($movie);
        $saveMethod = $repository->save(Argument::type(Movie::class));

        $movieService = $this->getMockBuilder(MovieService::class)
            ->onlyMethods(['fill'])
            ->setConstructorArgs([$repository->reveal()])
            ->getMock();

        $movieService
            ->expects(self::once())
            ->method('fill');

        // Act
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @var MovieService $movieService */
        $savedMovie = $movieService->update($movieId, $movieDto);

        // Assert
        $saveMethod->shouldBeCalled();
        self::assertSame($movie, $savedMovie);
    }

    protected function createMovieDto(): MovieDto
    {
        $movieDto = new MovieDto();
        $movieDto->title = 'title';
        $movieDto->description = 'description';
        $movieDto->link = 'link';
        $movieDto->image = 'image';
        $movieDto->pubDate = new \DateTimeImmutable();
        return $movieDto;
    }
}
