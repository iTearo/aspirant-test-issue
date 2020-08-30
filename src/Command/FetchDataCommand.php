<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Movie;
use App\Repository\Movie\MovieRepository;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
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

    private ClientInterface $httpClient;

    private LoggerInterface $logger;

    private MovieRepository $movieRepository;

    public function __construct(
        ClientInterface $httpClient,
        LoggerInterface $logger,
        MovieRepository $movieRepository,
        string $name = null
    ) {
        parent::__construct($name);
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->movieRepository = $movieRepository;
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

        if (!is_string($source)) {
            throw new RuntimeException('Source must be string');
        }

        $io = new SymfonyStyle($input, $output);

        $io->title(sprintf('Fetch data from %s', $source));

        try {
            $response = $this->httpClient->sendRequest(new Request('GET', $source));

        } catch (ClientExceptionInterface $e) {
            throw new RuntimeException($e->getMessage());
        }

        if (($status = $response->getStatusCode()) !== 200) {
            throw new RuntimeException(sprintf('Response status is %d, expected %d', $status, 200));
        }

        $data = $response->getBody()->getContents();

        $this->processXml($data, $maxNum);

        $this->logger->info(sprintf('End %s at %s', __CLASS__, (string) date_create()->format(DATE_ATOM)));

        return 0;
    }

    protected function processXml(string $data, int $maxNum): void
    {
        $xml = (new \SimpleXMLElement($data))->children();

        if (!property_exists($xml, 'channel')) {
            throw new RuntimeException('Could not find \'channel\' element in feed');
        }

        $namespace = $xml->getNamespaces(true)['content'];

        foreach ($xml->channel->item as $item) {
            if ($maxNum-- === 0) {
                break;
            }

            $movie = $this->getMovie((string) $item->title)
                ->setTitle((string) $item->title)
                ->setDescription((string) $item->description)
                ->setLink((string) $item->link)
                ->setPubDate($this->parseDate((string) $item->pubDate))
                ->setImage($this->parseImage($item, $namespace))
            ;

            $this->movieRepository->save($movie);
        }
    }

    protected function parseDate(string $date): \DateTimeImmutable
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return new \DateTimeImmutable($date);
    }

    protected function parseImage($item, $namespace): string
    {
        $itemContent = (string) $item->children($namespace)->encoded;
        preg_match('/src="https.*"/mU', $itemContent, $matches);
        return substr($matches[0], 5, -1);
    }

    protected function getMovie(string $title): Movie
    {
        $movie = $this->movieRepository->getByTitle($title);

        if ($movie === null) {
            $this->logger->info('Create new Movie', ['title' => $title]);
            $movie = new Movie();

        } else {
            $this->logger->info('Move found', ['title' => $title]);
        }

        return $movie;
    }
}
