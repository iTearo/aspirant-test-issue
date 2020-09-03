<?php

declare(strict_types=1);

namespace App\Command;

use Domain\Movie\Dto\MovieDto;
use \Lib\AppleTrailers\AppleTrailersClient;
use \Lib\AppleTrailers\Dto\Trailer;
use Domain\Movie\MovieService;
use JMS\Serializer\SerializerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FetchDataCommand extends Command
{
    protected static $defaultName = 'app:fetch:trailers';

    private const DEFAULT_SOURCE = 'https://trailers.apple.com/trailers/home/rss/newtrailers.rss';

    private const OPTION_SOURCE = 'source';

    private const DEFAULT_MAX_NUM = 10;

    private const OPTION_MAX_NUM = 'max-num';

    private SerializerInterface $serializer;

    private ClientInterface $httpClient;

    private LoggerInterface $logger;

    private MovieService $movieService;

    public function __construct(
        SerializerInterface $serializer,
        ClientInterface $httpClient,
        LoggerInterface $logger,
        MovieService $movieRepository,
        string $name = null
    ) {
        parent::__construct($name);
        $this->serializer = $serializer;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->movieService = $movieRepository;
    }

    public function configure(): void
    {
        $this
            ->setDescription('Fetch data from iTunes Movie Trailers')
            ->addOption(
                '--' . self::OPTION_SOURCE,
                null,InputArgument::OPTIONAL,
                'Overwrite source url',
                self::DEFAULT_SOURCE
            )
            ->addOption(
                '--' . self::OPTION_MAX_NUM,
                null, InputArgument::OPTIONAL,
                'Overwrite maximum number of loaded movies',
                self::DEFAULT_MAX_NUM
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->info(sprintf('Start %s at %s', __CLASS__, (string) date_create()->format(DATE_ATOM)));

        $source = $input->getOption(self::OPTION_SOURCE);

        $maxNum = (int) $input->getOption(self::OPTION_MAX_NUM);

        $io = new SymfonyStyle($input, $output);

        $io->title(sprintf('Fetch data from %s', $source));

        $trailers = $this->makeTrailersClient($source)->fetchTrailers($maxNum);

        $movieDtos = $this->makeMovieDtosFromTrailers($trailers);

        $this->createOrUpdateMovies($movieDtos);

        $this->logger->info(sprintf('End %s at %s', __CLASS__, (string) date_create()->format(DATE_ATOM)));

        return 0;
    }

    protected function makeTrailersClient(string $source): AppleTrailersClient
    {
        return new AppleTrailersClient($this->serializer, $this->httpClient, $source);
    }

    /**
     * @param MovieDto[] $movieDtos
     */
    protected function createOrUpdateMovies(array $movieDtos): void
    {
        foreach ($movieDtos as $movieDto) {
            $movie = $this->movieService->getByTitle($movieDto->title);

            if ($movie !== null) {
                $this->logger->info('Move found', ['title' => $movieDto->title]);

                /** @noinspection PhpUnhandledExceptionInspection */
                $this->movieService->update($movie->getId(), $movieDto);

            } else {
                $this->logger->info('Create new Movie', ['title' => $movieDto->title]);

                $this->movieService->create($movieDto);
            }
        }
    }

    /**
     * @param Trailer[] $trailers
     * @return MovieDto[]
     */
    protected function makeMovieDtosFromTrailers(array $trailers): array
    {
        $movies = [];

        foreach ($trailers as $trailer) {
            $movies[] = $this->makeMovieDtoFromTrailer($trailer);
        }

        return $movies;
    }

    protected function makeMovieDtoFromTrailer(Trailer $trailer): MovieDto
    {
        $movieDto = new MovieDto();
        $movieDto->title = $trailer->title;
        $movieDto->description = $trailer->description;
        $movieDto->link = $trailer->link;
        $movieDto->pubDate = $trailer->pubDate;
        $movieDto->image = $trailer->image;
        return $movieDto;
    }
}
